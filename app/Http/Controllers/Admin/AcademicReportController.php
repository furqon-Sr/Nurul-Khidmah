<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\PageContent;
use App\Models\RaporSemester;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Services\AcademicRaporService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AcademicReportController extends Controller
{
    public function __construct(
        private readonly AcademicRaporService $raporService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $activeYear = TahunAjaran::getAktif();
        $academicYears = TahunAjaran::orderByDesc('status_aktif')->orderByDesc('id')->get();

        // Filter Tahun Ajaran (default ke Tahun Ajaran Aktif pada kunjungan awal)
        if ($request->has('tahun_ajaran_id')) {
            $selectedYear = $request->filled('tahun_ajaran_id')
                ? $academicYears->firstWhere('id', (int) $request->input('tahun_ajaran_id'))
                : null;
        } else {
            $selectedYear = $activeYear;
        }

        $selectedTingkat = trim((string) $request->input('tingkat', ''));
        $search = trim((string) $request->input('search', ''));
        $filterKelasInput = $request->input('filter_kelas_id', $request->input('eval_kelas_id', ''));

        $classesQuery = Kelas::with(['waliKelas', 'tahunAjaran'])
            ->orderBy('tingkat_kelas')
            ->orderBy('nama_kelas');

        if ($user->isTenagaPengajar()) {
            $pengajarId = $user->pengajar?->id ?? 0;
            $waliClassIds = Kelas::where('wali_kelas_id', $pengajarId)->pluck('id');

            if ($waliClassIds->isNotEmpty()) {
                $classesQuery->whereIn('id', $waliClassIds);
            } else {
                $taughtClassIds = JadwalPelajaran::where('pengajar_id', $pengajarId)->pluck('kelas_id')->unique();
                $classesQuery->whereIn('id', $taughtClassIds);
            }
        }

        $classes = $classesQuery->get();
        $allowedClassIds = $classes->pluck('id');

        // Filter daftar kelas sesuai Tahun Ajaran & Tingkat (7 / 8 / 9)
        $filteredClasses = $classes->filter(function (Kelas $c) use ($selectedYear, $selectedTingkat) {
            if ($selectedYear && $c->tahun_ajaran_id && (int) $c->tahun_ajaran_id !== (int) $selectedYear->id) {
                return false;
            }
            if ($selectedTingkat !== '') {
                $haystack = strtolower(($c->tingkat_kelas ?? '').' '.($c->nama_kelas ?? ''));
                if (! str_contains($haystack, strtolower($selectedTingkat))) {
                    return false;
                }
            }

            return true;
        })->values();

        // Fallback apabila kunjungan awal memiliki kelas lintas tahun ajaran
        if (! $request->has('tahun_ajaran_id') && $selectedTingkat === '' && $filteredClasses->isEmpty() && $classes->isNotEmpty()) {
            $filteredClasses = $classes;
        }

        $selectedFilterKelas = filled($filterKelasInput)
            ? $classes->firstWhere('id', (int) $filterKelasInput)
            : null;

        // Query santri terfilter (terhubung langsung ke daftar santri & tombol Cetak Rapor PDF)
        $santrisQuery = Santri::with(['kelas.waliKelas', 'kelas.tahunAjaran', 'raporSemesters'])
            ->whereIn('status_santri', ['Aktif', 'Lulus'])
            ->orderBy('nama_lengkap');

        if ($user->isTenagaPengajar()) {
            $santrisQuery->where(function ($q) use ($allowedClassIds) {
                $q->whereIn('kelas_id', $allowedClassIds)
                    ->orWhereHas('riwayatKelas', fn ($rq) => $rq->whereIn('kelas_baru_id', $allowedClassIds)->orWhereIn('kelas_lama_id', $allowedClassIds));
            });
        }

        if ($selectedFilterKelas) {
            $kId = $selectedFilterKelas->id;
            $santrisQuery->where(function ($q) use ($kId, $selectedYear) {
                $q->where('kelas_id', $kId)
                    ->orWhereHas('riwayatKelas', function ($rq) use ($kId, $selectedYear) {
                        $rq->where('kelas_baru_id', $kId);
                        if ($selectedYear) {
                            $rq->where('tahun_ajaran_id', $selectedYear->id);
                        }
                    });
            });
        } elseif ($selectedTingkat !== '') {
            $matchingClassIds = $filteredClasses->pluck('id');
            $santrisQuery->where(function ($q) use ($matchingClassIds, $selectedYear) {
                $q->whereIn('kelas_id', $matchingClassIds)
                    ->orWhereHas('riwayatKelas', function ($rq) use ($matchingClassIds, $selectedYear) {
                        $rq->whereIn('kelas_baru_id', $matchingClassIds);
                        if ($selectedYear) {
                            $rq->where('tahun_ajaran_id', $selectedYear->id);
                        }
                    });
            });
        } elseif ($selectedYear && $request->filled('tahun_ajaran_id')) {
            $yearClassIds = $classes->where('tahun_ajaran_id', $selectedYear->id)->pluck('id');
            $santrisQuery->where(function ($q) use ($yearClassIds, $selectedYear) {
                $q->whereIn('kelas_id', $yearClassIds)
                    ->orWhereHas('riwayatKelas', fn ($rq) => $rq->where('tahun_ajaran_id', $selectedYear->id))
                    ->orWhereHas('nilais.jadwalPelajaran', fn ($nq) => $nq->where('tahun_ajaran_id', $selectedYear->id));
            });
        }

        if ($search !== '') {
            $santrisQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $santris = $santrisQuery->get();

        // Kelas acuan untuk form simpan evaluasi Wali Kelas
        $selectedEvalKelas = $selectedFilterKelas
            ?? ($filteredClasses->count() === 1 ? $filteredClasses->first() : $filteredClasses->first() ?? $classes->first());

        $evalSantris = $santris;

        // Daftar Mata Pelajaran untuk opsi cetak nilai spesifik 1 mapel
        $subjectsQuery = MataPelajaran::orderBy('nama_mapel');
        if ($user->isTenagaPengajar()) {
            $pengajarId = $user->pengajar?->id ?? 0;
            $mapelIds = JadwalPelajaran::whereIn('kelas_id', $allowedClassIds)
                ->orWhere('pengajar_id', $pengajarId)
                ->pluck('mata_pelajaran_id')
                ->unique();
            if ($mapelIds->isNotEmpty()) {
                $subjectsQuery->whereIn('id', $mapelIds);
            }
        }
        $subjects = $subjectsQuery->get();

        return view('reports.index', compact(
            'classes',
            'filteredClasses',
            'academicYears',
            'selectedYear',
            'selectedTingkat',
            'selectedFilterKelas',
            'search',
            'santris',
            'activeYear',
            'selectedEvalKelas',
            'evalSantris',
            'subjects'
        ));
    }

    /**
     * Simpan kolektif Penilaian Sikap/Kepesantrenan & Catatan Wali Kelas untuk Rapor
     */
    public function updateWaliNotes(Request $request, Kelas $kelas)
    {
        $this->authorizeClassAccess($kelas);

        $validated = $request->validate([
            'evaluasi' => 'required|array',
            'evaluasi.*.nilai_sikap' => 'nullable|string|max:50',
            'evaluasi.*.catatan_wali_kelas' => 'nullable|string|max:1000',
            'allow_filtered' => 'nullable|boolean',
        ]);

        $allowFiltered = $request->boolean('allow_filtered');

        foreach ($validated['evaluasi'] as $santriId => $data) {
            $santri = Santri::with('kelas')->find($santriId);
            if (! $santri) {
                continue;
            }

            if ((int) $santri->kelas_id !== (int) $kelas->id) {
                if (! $allowFiltered || ! $santri->kelas) {
                    continue;
                }
                $this->authorizeClassAccess($santri->kelas);
            }

            $targetKelas = $santri->kelas ?? $kelas;
            $targetTaId = $targetKelas->tahun_ajaran_id ?? $kelas->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;

            if ($targetTaId) {
                RaporSemester::updateOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'tahun_ajaran_id' => $targetTaId,
                    ],
                    [
                        'kelas_id' => $targetKelas->id,
                        'nilai_sikap' => filled($data['nilai_sikap'] ?? null) ? $data['nilai_sikap'] : null,
                        'catatan_wali_kelas' => filled($data['catatan_wali_kelas'] ?? null) ? trim($data['catatan_wali_kelas']) : null,
                        'status_publikasi' => 'published',
                    ]
                );
            }
        }

        AdminActivityLog::log('update', "Memperbarui nilai sikap & catatan wali kelas rapor ({$kelas->nama_kelas})", 'Kelas', $kelas->id);

        return redirect()->route('admin.reports.index', array_filter([
            'tahun_ajaran_id' => $request->input('tahun_ajaran_id'),
            'tingkat' => $request->input('tingkat'),
            'filter_kelas_id' => $request->input('filter_kelas_id', $kelas->id),
            'search' => $request->input('search'),
        ]))->with('success', 'Penilaian sikap & catatan wali kelas berhasil disimpan.');
    }

    public function exportAttendancePdf(Request $request)
    {
        if ($request->has('download') || $request->input('format') === 'pdf') {
            return $this->downloadAttendancePdf($request);
        }

        $request->validate([
            'kelas_id' => 'required|exists:kelasses,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        $targetYearId = $request->filled('tahun_ajaran_id')
            ? (int) $request->tahun_ajaran_id
            : null;

        $kelas = Kelas::with([
            'waliKelas',
            'tahunAjaran',
            'santris' => fn ($sq) => $sq->whereIn('status_santri', ['Aktif', 'Lulus'])->orderBy('nama_lengkap'),
            'santris.absensis' => function ($aq) use ($targetYearId, $request) {
                $aq->whereHas('jadwalPelajaran', function ($jq) use ($request, $targetYearId) {
                    $jq->where('kelas_id', $request->kelas_id);
                    if ($targetYearId) {
                        $jq->where('tahun_ajaran_id', $targetYearId);
                    }
                });
            },
            'santris.raporSemesters',
        ])->findOrFail($request->kelas_id);
        $this->authorizeClassAccess($kelas);

        $tahunAjaran = $targetYearId
            ? TahunAjaran::findOrFail($targetYearId)
            : ($kelas->tahunAjaran ?? TahunAjaran::getAktif());

        $santris = $this->resolveSantrisForClassReport($kelas, $tahunAjaran);

        $absensiSummary = [];
        foreach ($santris as $santri) {
            $records = $santri->absensis;
            $absensiSummary[$santri->id] = [
                'hadir' => $records->where('status_kehadiran', 'Hadir')->count(),
                'izin' => $records->where('status_kehadiran', 'Izin')->count(),
                'sakit' => $records->where('status_kehadiran', 'Sakit')->count(),
                'alpa' => $records->where('status_kehadiran', 'Alpa')->count(),
                'terlambat' => $records->where('status_kehadiran', 'Terlambat')->count(),
                'bolos' => $records->where('status_kehadiran', 'Bolos')->count(),
                'catatan_count' => $records->filter(fn ($r) => filled($r->keterangan))->count(),
            ];
        }

        $siteInfo = PageContent::all()->keyBy('section_key');
        AdminActivityLog::log('export', "Mencetak PDF Rekap Presensi Kelas {$kelas->nama_kelas}", 'Kelas', $kelas->id);

        return view('reports.attendance-pdf', compact('kelas', 'tahunAjaran', 'santris', 'absensiSummary', 'siteInfo'));
    }

    public function downloadAttendancePdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelasses,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        $targetYearId = $request->filled('tahun_ajaran_id')
            ? (int) $request->tahun_ajaran_id
            : null;

        $kelas = Kelas::with([
            'waliKelas',
            'tahunAjaran',
            'santris' => fn ($sq) => $sq->whereIn('status_santri', ['Aktif', 'Lulus'])->orderBy('nama_lengkap'),
            'santris.absensis' => function ($aq) use ($targetYearId, $request) {
                $aq->whereHas('jadwalPelajaran', function ($jq) use ($request, $targetYearId) {
                    $jq->where('kelas_id', $request->kelas_id);
                    if ($targetYearId) {
                        $jq->where('tahun_ajaran_id', $targetYearId);
                    }
                });
            },
            'santris.raporSemesters',
        ])->findOrFail($request->kelas_id);
        $this->authorizeClassAccess($kelas);

        $tahunAjaran = $targetYearId
            ? TahunAjaran::findOrFail($targetYearId)
            : ($kelas->tahunAjaran ?? TahunAjaran::getAktif());

        $santris = $this->resolveSantrisForClassReport($kelas, $tahunAjaran);

        $absensiSummary = [];
        foreach ($santris as $santri) {
            $records = $santri->absensis;
            $absensiSummary[$santri->id] = [
                'hadir' => $records->where('status_kehadiran', 'Hadir')->count(),
                'izin' => $records->where('status_kehadiran', 'Izin')->count(),
                'sakit' => $records->where('status_kehadiran', 'Sakit')->count(),
                'alpa' => $records->where('status_kehadiran', 'Alpa')->count(),
                'terlambat' => $records->where('status_kehadiran', 'Terlambat')->count(),
                'bolos' => $records->where('status_kehadiran', 'Bolos')->count(),
                'catatan_count' => $records->filter(fn ($r) => filled($r->keterangan))->count(),
            ];
        }

        $siteInfo = PageContent::all()->keyBy('section_key');
        AdminActivityLog::log('export', "Mendownload PDF Rekap Presensi Kelas {$kelas->nama_kelas}", 'Kelas', $kelas->id);

        $viewData = compact('kelas', 'tahunAjaran', 'santris', 'absensiSummary', 'siteInfo');
        $viewData['isPdf'] = true;
        $viewData['pageOrientation'] = 'portrait';

        $pdf = Pdf::loadView('reports.attendance-pdf', $viewData)
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $fileName = 'Rekap_Absensi_'.Str::slug($kelas->nama_kelas).'.pdf';

        return $pdf->download($fileName);
    }

    public function exportGradesPdf(Request $request)
    {
        if ($request->has('download') || $request->input('format') === 'pdf') {
            return $this->downloadGradesPdf($request);
        }

        $request->validate([
            'kelas_id' => 'required|exists:kelasses,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajarans,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        $targetYearId = $request->filled('tahun_ajaran_id')
            ? (int) $request->tahun_ajaran_id
            : null;

        $kelas = Kelas::with([
            'waliKelas',
            'tahunAjaran',
            'santris' => fn ($sq) => $sq->whereIn('status_santri', ['Aktif', 'Lulus'])->orderBy('nama_lengkap'),
            'santris.nilais' => function ($nq) use ($targetYearId, $request) {
                $nq->whereHas('jadwalPelajaran', function ($jq) use ($request, $targetYearId) {
                    $jq->where('kelas_id', $request->kelas_id);
                    if ($targetYearId) {
                        $jq->where('tahun_ajaran_id', $targetYearId);
                    }
                });
            },
            'santris.nilais.mataPelajaran',
            'santris.nilais.gradeComponent',
            'santris.nilais.jadwalPelajaran.mataPelajaran',
            'santris.nilais.jadwalPelajaran.pengajar',
            'santris.absensis' => function ($aq) use ($targetYearId, $request) {
                $aq->whereHas('jadwalPelajaran', function ($jq) use ($request, $targetYearId) {
                    $jq->where('kelas_id', $request->kelas_id);
                    if ($targetYearId) {
                        $jq->where('tahun_ajaran_id', $targetYearId);
                    }
                });
            },
            'santris.raporSemesters',
        ])->findOrFail($request->kelas_id);
        $this->authorizeClassAccess($kelas);

        $tahunAjaran = $targetYearId
            ? TahunAjaran::findOrFail($targetYearId)
            : ($kelas->tahunAjaran ?? TahunAjaran::getAktif());

        $selectedSubject = $request->filled('mata_pelajaran_id')
            ? MataPelajaran::findOrFail($request->mata_pelajaran_id)
            : null;

        $santris = $this->resolveSantrisForClassReport($kelas, $tahunAjaran);

        $schedules = JadwalPelajaran::with(['mataPelajaran', 'pengajar'])
            ->where('kelas_id', $kelas->id)
            ->when($tahunAjaran, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->get();

        $allGradesInClass = Nilai::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.pengajar'])
            ->whereIn('santri_id', $santris->pluck('id'))
            ->whereHas('jadwalPelajaran', function ($jq) use ($kelas, $tahunAjaran) {
                $jq->where('kelas_id', $kelas->id);
                if ($tahunAjaran) {
                    $jq->where('tahun_ajaran_id', $tahunAjaran->id);
                }
            })
            ->get();

        // Daftar unik seluruh Mata Pelajaran pada kelas tersebut
        $subjectMap = collect();
        foreach ($schedules->groupBy('mata_pelajaran_id') as $mapelId => $schedGroup) {
            $first = $schedGroup->first();
            if ($first && $first->mataPelajaran) {
                $subjectMap->put($mapelId, (object) [
                    'id' => $mapelId,
                    'kode_mapel' => $first->mataPelajaran->kode_mapel,
                    'nama_mapel' => $first->mataPelajaran->nama_mapel,
                    'pengajar_nama' => $first->pengajar?->nama_lengkap ?? '-',
                ]);
            }
        }

        foreach ($allGradesInClass as $nilaiRow) {
            $m = $nilaiRow->jadwalPelajaran?->mataPelajaran;
            if ($m && ! $subjectMap->has($m->id)) {
                $subjectMap->put($m->id, (object) [
                    'id' => $m->id,
                    'kode_mapel' => $m->kode_mapel,
                    'nama_mapel' => $m->nama_mapel,
                    'pengajar_nama' => $nilaiRow->jadwalPelajaran?->pengajar?->nama_lengkap ?? '-',
                ]);
            }
        }

        if ($selectedSubject && ! $subjectMap->has($selectedSubject->id)) {
            $subjectMap->put($selectedSubject->id, (object) [
                'id' => $selectedSubject->id,
                'kode_mapel' => $selectedSubject->kode_mapel,
                'nama_mapel' => $selectedSubject->nama_mapel,
                'pengajar_nama' => '-',
            ]);
        }

        $classSubjects = $subjectMap->sortBy('nama_mapel')->values();
        $totalSubjectsInClass = $classSubjects->count();

        $gradesBySantri = [];
        $scoreMatrix = [];
        $santriStats = [];

        foreach ($santris as $santri) {
            $santriGrades = $allGradesInClass->where('santri_id', $santri->id)->values();
            $gradesBySantri[$santri->id] = $santriGrades;

            $matrixRow = [];
            foreach ($santriGrades as $g) {
                $mId = $g->jadwalPelajaran?->mata_pelajaran_id;
                if ($mId) {
                    $matrixRow[$mId] = $g;
                }
            }
            $scoreMatrix[$santri->id] = $matrixRow;

            $nilaiValues = collect($matrixRow)->pluck('nilai_akhir')->map(fn ($v) => (float) $v);
            $jumlah = $nilaiValues->isNotEmpty() ? round($nilaiValues->sum(), 2) : 0.0;
            $avg = $nilaiValues->isNotEmpty() ? round($nilaiValues->avg(), 2) : 0.0;

            $santriStats[$santri->id] = [
                'graded_count' => $nilaiValues->count(),
                'jumlah' => $jumlah,
                'rata_rata' => $avg,
                'predikat' => $nilaiValues->isNotEmpty() ? Nilai::calculateGrade($avg) : '-',
            ];
        }

        $selectedSubjectTeacher = $selectedSubject
            ? ($subjectMap->get($selectedSubject->id)?->pengajar_nama ?? '-')
            : null;

        $siteInfo = PageContent::all()->keyBy('section_key');
        $logDesc = $selectedSubject
            ? "Mencetak PDF Daftar Nilai Mapel {$selectedSubject->nama_mapel} — Kelas {$kelas->nama_kelas}"
            : "Mencetak PDF Leger Nilai Lengkap Kelas {$kelas->nama_kelas}";
        AdminActivityLog::log('export', $logDesc, 'Kelas', $kelas->id);

        return view('reports.grades-pdf', compact(
            'kelas',
            'tahunAjaran',
            'santris',
            'classSubjects',
            'gradesBySantri',
            'scoreMatrix',
            'santriStats',
            'totalSubjectsInClass',
            'selectedSubject',
            'selectedSubjectTeacher',
            'siteInfo'
        ));
    }

    public function downloadGradesPdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelasses,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajarans,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        $targetYearId = $request->filled('tahun_ajaran_id')
            ? (int) $request->tahun_ajaran_id
            : null;

        $kelas = Kelas::with([
            'waliKelas',
            'tahunAjaran',
            'santris' => fn ($sq) => $sq->whereIn('status_santri', ['Aktif', 'Lulus'])->orderBy('nama_lengkap'),
            'santris.nilais' => function ($nq) use ($targetYearId, $request) {
                $nq->whereHas('jadwalPelajaran', function ($jq) use ($request, $targetYearId) {
                    $jq->where('kelas_id', $request->kelas_id);
                    if ($targetYearId) {
                        $jq->where('tahun_ajaran_id', $targetYearId);
                    }
                });
            },
            'santris.nilais.mataPelajaran',
            'santris.nilais.gradeComponent',
            'santris.nilais.jadwalPelajaran.mataPelajaran',
            'santris.nilais.jadwalPelajaran.pengajar',
            'santris.absensis' => function ($aq) use ($targetYearId, $request) {
                $aq->whereHas('jadwalPelajaran', function ($jq) use ($request, $targetYearId) {
                    $jq->where('kelas_id', $request->kelas_id);
                    if ($targetYearId) {
                        $jq->where('tahun_ajaran_id', $targetYearId);
                    }
                });
            },
            'santris.raporSemesters',
        ])->findOrFail($request->kelas_id);
        $this->authorizeClassAccess($kelas);

        $tahunAjaran = $targetYearId
            ? TahunAjaran::findOrFail($targetYearId)
            : ($kelas->tahunAjaran ?? TahunAjaran::getAktif());

        $selectedSubject = $request->filled('mata_pelajaran_id')
            ? MataPelajaran::findOrFail($request->mata_pelajaran_id)
            : null;

        $santris = $this->resolveSantrisForClassReport($kelas, $tahunAjaran);

        $schedules = JadwalPelajaran::with(['mataPelajaran', 'pengajar'])
            ->where('kelas_id', $kelas->id)
            ->when($tahunAjaran, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->get();

        $allGradesInClass = Nilai::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.pengajar'])
            ->whereIn('santri_id', $santris->pluck('id'))
            ->whereHas('jadwalPelajaran', function ($jq) use ($kelas, $tahunAjaran) {
                $jq->where('kelas_id', $kelas->id);
                if ($tahunAjaran) {
                    $jq->where('tahun_ajaran_id', $tahunAjaran->id);
                }
            })
            ->get();

        $subjectMap = collect();
        foreach ($schedules->groupBy('mata_pelajaran_id') as $mapelId => $schedGroup) {
            $first = $schedGroup->first();
            if ($first && $first->mataPelajaran) {
                $subjectMap->put($mapelId, (object) [
                    'id' => $mapelId,
                    'kode_mapel' => $first->mataPelajaran->kode_mapel,
                    'nama_mapel' => $first->mataPelajaran->nama_mapel,
                    'pengajar_nama' => $first->pengajar?->nama_lengkap ?? '-',
                ]);
            }
        }

        foreach ($allGradesInClass as $nilaiRow) {
            $m = $nilaiRow->jadwalPelajaran?->mataPelajaran;
            if ($m && ! $subjectMap->has($m->id)) {
                $subjectMap->put($m->id, (object) [
                    'id' => $m->id,
                    'kode_mapel' => $m->kode_mapel,
                    'nama_mapel' => $m->nama_mapel,
                    'pengajar_nama' => $nilaiRow->jadwalPelajaran?->pengajar?->nama_lengkap ?? '-',
                ]);
            }
        }

        if ($selectedSubject && ! $subjectMap->has($selectedSubject->id)) {
            $subjectMap->put($selectedSubject->id, (object) [
                'id' => $selectedSubject->id,
                'kode_mapel' => $selectedSubject->kode_mapel,
                'nama_mapel' => $selectedSubject->nama_mapel,
                'pengajar_nama' => '-',
            ]);
        }

        $classSubjects = $subjectMap->sortBy('nama_mapel')->values();
        $totalSubjectsInClass = $classSubjects->count();

        $gradesBySantri = [];
        $scoreMatrix = [];
        $santriStats = [];

        foreach ($santris as $santri) {
            $santriGrades = $allGradesInClass->where('santri_id', $santri->id)->values();
            $gradesBySantri[$santri->id] = $santriGrades;

            $matrixRow = [];
            foreach ($santriGrades as $g) {
                $mId = $g->jadwalPelajaran?->mata_pelajaran_id;
                if ($mId) {
                    $matrixRow[$mId] = $g;
                }
            }
            $scoreMatrix[$santri->id] = $matrixRow;

            $nilaiValues = collect($matrixRow)->pluck('nilai_akhir')->map(fn ($v) => (float) $v);
            $jumlah = $nilaiValues->isNotEmpty() ? round($nilaiValues->sum(), 2) : 0.0;
            $avg = $nilaiValues->isNotEmpty() ? round($nilaiValues->avg(), 2) : 0.0;

            $santriStats[$santri->id] = [
                'graded_count' => $nilaiValues->count(),
                'jumlah' => $jumlah,
                'rata_rata' => $avg,
                'predikat' => $nilaiValues->isNotEmpty() ? Nilai::calculateGrade($avg) : '-',
            ];
        }

        $selectedSubjectTeacher = $selectedSubject
            ? ($subjectMap->get($selectedSubject->id)?->pengajar_nama ?? '-')
            : null;

        $siteInfo = PageContent::all()->keyBy('section_key');
        $logDesc = $selectedSubject
            ? "Mendownload PDF Capaian Nilai Mapel {$selectedSubject->nama_mapel} — Kelas {$kelas->nama_kelas}"
            : "Mendownload PDF Leger Nilai Lengkap Kelas {$kelas->nama_kelas}";
        AdminActivityLog::log('export', $logDesc, 'Kelas', $kelas->id);

        $viewData = compact(
            'kelas',
            'tahunAjaran',
            'santris',
            'classSubjects',
            'gradesBySantri',
            'scoreMatrix',
            'santriStats',
            'totalSubjectsInClass',
            'selectedSubject',
            'selectedSubjectTeacher',
            'siteInfo'
        );
        $viewData['isPdf'] = true;
        $orientation = $selectedSubject ? 'portrait' : 'landscape';
        $viewData['pageOrientation'] = $orientation;

        $pdf = Pdf::loadView('reports.grades-pdf', $viewData)
            ->setPaper('a4', $orientation)
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $fileName = ($selectedSubject ? 'Nilai_'.Str::slug($selectedSubject->nama_mapel) : 'Leger_Nilai').'_'.Str::slug($kelas->nama_kelas).'.pdf';

        return $pdf->download($fileName);
    }

    public function exportSantriRaporPdf(Request $request, Santri $santri)
    {
        if ($request->has('download') || $request->input('format') === 'pdf') {
            return $this->downloadSantriRaporPdf($request, $santri);
        }

        $santri->load([
            'kelas.waliKelas',
            'kelas.tahunAjaran',
            'kelas.educationalUnit.headmaster',
            'raporSemesters',
            'nilais.mataPelajaran',
            'nilais.gradeComponent',
            'nilais.jadwalPelajaran.mataPelajaran',
            'nilais.jadwalPelajaran.pengajar',
            'absensis',
        ]);

        $tahunAjaran = $request->filled('tahun_ajaran_id')
            ? TahunAjaran::findOrFail($request->tahun_ajaran_id)
            : (TahunAjaran::getAktif() ?? $santri->kelas?->tahunAjaran);

        $kelasAcuan = $this->raporService->resolveKelasForSantriInYear($santri, $tahunAjaran) ?? $santri->kelas;
        if ($kelasAcuan) {
            $this->authorizeClassAccess($kelasAcuan);
        }

        $raporData = $this->raporService->buildSantriRaporData($santri, $tahunAjaran);
        $siteInfo = PageContent::all()->keyBy('section_key');

        AdminActivityLog::log('export', "Mencetak Rapor PDF Santri: {$santri->nama_lengkap} ({$santri->nis})", 'Santri', $santri->id);

        return view('santri.rapor-pdf', [
            'santri' => $santri,
            'tahunAjaran' => $tahunAjaran,
            'kelasPeriode' => $raporData['kelasPeriode'],
            'subjectRows' => $raporData['subjectRows'],
            'nilais' => $raporData['nilais'],
            'gradedCount' => $raporData['gradedCount'],
            'totalMapelCount' => $raporData['totalMapelCount'],
            'jumlahNilai' => $raporData['jumlahNilai'],
            'rataRata' => $raporData['rataRata'],
            'totalHadir' => $raporData['totalHadir'],
            'totalIzin' => $raporData['totalIzin'],
            'totalSakit' => $raporData['totalSakit'],
            'totalAlpa' => $raporData['totalAlpa'],
            'raporSemester' => $raporData['raporSemester'],
            'catatanWaliKelas' => $raporData['catatanWaliKelas'],
            'nilaiSikap' => $raporData['nilaiSikap'],
            'siteInfo' => $siteInfo,
        ]);
    }

    public function downloadSantriRaporPdf(Request $request, Santri $santri)
    {
        $santri->load([
            'kelas.waliKelas',
            'kelas.tahunAjaran',
            'kelas.educationalUnit.headmaster',
            'raporSemesters',
            'nilais.mataPelajaran',
            'nilais.gradeComponent',
            'nilais.jadwalPelajaran.mataPelajaran',
            'nilais.jadwalPelajaran.pengajar',
            'absensis',
        ]);

        $tahunAjaran = $request->filled('tahun_ajaran_id')
            ? TahunAjaran::findOrFail($request->tahun_ajaran_id)
            : (TahunAjaran::getAktif() ?? $santri->kelas?->tahunAjaran);

        $kelasAcuan = $this->raporService->resolveKelasForSantriInYear($santri, $tahunAjaran) ?? $santri->kelas;
        if ($kelasAcuan) {
            $this->authorizeClassAccess($kelasAcuan);
        }

        $raporData = $this->raporService->buildSantriRaporData($santri, $tahunAjaran);
        $siteInfo = PageContent::all()->keyBy('section_key');

        AdminActivityLog::log('export', "Mendownload Rapor PDF Asli Santri: {$santri->nama_lengkap} ({$santri->nis})", 'Santri', $santri->id);

        $viewData = [
            'santri' => $santri,
            'tahunAjaran' => $tahunAjaran,
            'kelasPeriode' => $raporData['kelasPeriode'],
            'subjectRows' => $raporData['subjectRows'],
            'nilais' => $raporData['nilais'],
            'gradedCount' => $raporData['gradedCount'],
            'totalMapelCount' => $raporData['totalMapelCount'],
            'jumlahNilai' => $raporData['jumlahNilai'],
            'rataRata' => $raporData['rataRata'],
            'totalHadir' => $raporData['totalHadir'],
            'totalIzin' => $raporData['totalIzin'],
            'totalSakit' => $raporData['totalSakit'],
            'totalAlpa' => $raporData['totalAlpa'],
            'raporSemester' => $raporData['raporSemester'],
            'catatanWaliKelas' => $raporData['catatanWaliKelas'],
            'nilaiSikap' => $raporData['nilaiSikap'],
            'siteInfo' => $siteInfo,
            'isPdf' => true,
            'pageOrientation' => 'portrait',
        ];

        $pdf = Pdf::loadView('santri.rapor-pdf', $viewData)
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        $fileName = 'Rapor_'.Str::slug($santri->nama_lengkap).'_'.($santri->nis).'.pdf';

        return $pdf->download($fileName);
    }

    private function resolveSantrisForClassReport(Kelas $kelas, ?TahunAjaran $tahunAjaran)
    {
        $taId = $tahunAjaran?->id;

        return Santri::with([
            'kelas.waliKelas',
            'kelas.tahunAjaran',
            'raporSemesters',
            'absensis' => function ($aq) use ($kelas, $taId) {
                $aq->whereHas('jadwalPelajaran', function ($jq) use ($kelas, $taId) {
                    $jq->where('kelas_id', $kelas->id);
                    if ($taId) {
                        $jq->where('tahun_ajaran_id', $taId);
                    }
                });
            },
            'nilais' => function ($nq) use ($kelas, $taId) {
                $nq->whereHas('jadwalPelajaran', function ($jq) use ($kelas, $taId) {
                    $jq->where('kelas_id', $kelas->id);
                    if ($taId) {
                        $jq->where('tahun_ajaran_id', $taId);
                    }
                });
            },
            'nilais.mataPelajaran',
            'nilais.gradeComponent',
            'nilais.jadwalPelajaran.mataPelajaran',
            'nilais.jadwalPelajaran.pengajar',
        ])
            ->whereIn('status_santri', ['Aktif', 'Lulus'])
            ->where(function ($q) use ($kelas, $tahunAjaran) {
                $q->where('kelas_id', $kelas->id)
                    ->orWhereHas('riwayatKelas', function ($rq) use ($kelas, $tahunAjaran) {
                        $rq->where('kelas_baru_id', $kelas->id);
                        if ($tahunAjaran) {
                            $rq->where('tahun_ajaran_id', $tahunAjaran->id);
                        }
                    })
                    ->orWhereHas('nilais.jadwalPelajaran', function ($nq) use ($kelas, $tahunAjaran) {
                        $nq->where('kelas_id', $kelas->id);
                        if ($tahunAjaran) {
                            $nq->where('tahun_ajaran_id', $tahunAjaran->id);
                        }
                    });
            })
            ->orderBy('nama_lengkap')
            ->get();
    }

    private function authorizeClassAccess(Kelas $kelas): void
    {
        $user = Auth::user();
        if (! $user->isTenagaPengajar()) {
            return;
        }

        $pengajarId = $user->pengajar?->id ?? 0;
        $isWaliKelas = $kelas->wali_kelas_id === $pengajarId;
        $teachesInClass = JadwalPelajaran::where('kelas_id', $kelas->id)
            ->where('pengajar_id', $pengajarId)
            ->exists();

        if (! $isWaliKelas && ! $teachesInClass) {
            abort(403, 'Anda hanya memiliki akses laporan pada kelas perwalian atau kelas yang Anda ampu.');
        }
    }

    public function approveSantri(Request $request, Santri $santri)
    {
        $user = Auth::user();
        if (! $user->isKepalaSekolah() && ! $user->isPimpinan() && ! $user->isSuperAdmin()) {
            abort(403, 'Hanya Kepala Sekolah atau Pimpinan yang berwenang menyetujui (approve) rapor santri.');
        }

        $activeYear = TahunAjaran::getAktif();
        $targetTaId = $request->input('tahun_ajaran_id', $santri->kelas?->tahun_ajaran_id ?? $activeYear?->id);

        if ($targetTaId) {
            RaporSemester::updateOrCreate(
                [
                    'santri_id' => $santri->id,
                    'tahun_ajaran_id' => $targetTaId,
                ],
                [
                    'kelas_id' => $santri->kelas_id,
                    'status_publikasi' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]
            );
        }

        AdminActivityLog::log('approve', "Menyetujui (approve) rapor santri: {$santri->nama_lengkap} ({$santri->nis})", 'RaporSemester', $santri->id);

        return redirect()->back()->with('success', "Rapor santri {$santri->nama_lengkap} berhasil disetujui (Approved). Nilai dan presensi kini terkunci.");
    }

    public function approveClass(Request $request, Kelas $kelas)
    {
        $user = Auth::user();
        if (! $user->isKepalaSekolah() && ! $user->isPimpinan() && ! $user->isSuperAdmin()) {
            abort(403, 'Hanya Kepala Sekolah atau Pimpinan yang berwenang menyetujui (approve) rapor kelas.');
        }

        $activeYear = TahunAjaran::getAktif();
        $targetTaId = $request->input('tahun_ajaran_id', $kelas->tahun_ajaran_id ?? $activeYear?->id);

        $kelas->load('santris');
        $count = 0;

        foreach ($kelas->santris as $santri) {
            if ($targetTaId) {
                RaporSemester::updateOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'tahun_ajaran_id' => $targetTaId,
                    ],
                    [
                        'kelas_id' => $kelas->id,
                        'status_publikasi' => 'approved',
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                    ]
                );
                $count++;
            }
        }

        AdminActivityLog::log('approve', "Menyetujui (approve) seluruh rapor kelas: {$kelas->nama_kelas} ({$count} santri)", 'Kelas', $kelas->id);

        return redirect()->back()->with('success', "Seluruh rapor kelas {$kelas->nama_kelas} ({$count} santri) berhasil disetujui (Approved). Nilai dan presensi kini terkunci.");
    }

    public function approveReport(Request $request)
    {
        $user = Auth::user();
        if (! $user->isKepalaSekolah() && ! $user->isPimpinan() && ! $user->isSuperAdmin()) {
            abort(403, 'Hanya Kepala Sekolah atau Pimpinan yang berwenang menyetujui (approve) rapor.');
        }

        $validated = $request->validate([
            'kelas_id' => 'nullable|exists:kelasses,id',
            'santri_id' => 'nullable|exists:santris,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        if (! empty($validated['santri_id'])) {
            $santri = Santri::with('kelas')->findOrFail($validated['santri_id']);

            return $this->approveSantri($request, $santri);
        }

        if (! empty($validated['kelas_id'])) {
            $kelas = Kelas::findOrFail($validated['kelas_id']);

            return $this->approveClass($request, $kelas);
        }

        return redirect()->back()->with('error', 'Silakan tentukan kelas atau santri yang ingin disetujui.');
    }
}
