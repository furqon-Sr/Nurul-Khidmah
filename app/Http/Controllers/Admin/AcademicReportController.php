<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
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

        $requestedTab = (string) $request->input('tab', '');
        $activeTab = in_array($requestedTab, ['laporan', 'kelola', 'persetujuan', 'riwayat'], true)
            ? $requestedTab
            : 'laporan';

        // Query santri terfilter (terhubung langsung ke daftar santri & tombol Cetak Rapor PDF)
        $santrisQuery = Santri::with([
            'kelas.waliKelas',
            'kelas.tahunAjaran',
            'raporSemesters.approvedBy',
            'raporSemesters.publishedBy',
            'raporSemesters.validatedBy',
            'raporSemesters.revisionRequestedBy',
            'riwayatKelas.kelasBaru.waliKelas',
            'riwayatKelas.kelasLama.waliKelas',
        ])
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
                    })
                    ->orWhereHas('riwayatKelas', function ($rq) use ($kId, $selectedYear) {
                        $rq->where('kelas_lama_id', $kId);
                        if ($selectedYear) {
                            $rq->whereHas('kelasLama', fn ($kq) => $kq->where('tahun_ajaran_id', $selectedYear->id));
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
                    ->orWhereHas('riwayatKelas.kelasLama', fn ($kq) => $kq->where('tahun_ajaran_id', $selectedYear->id))
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

        // Kelas acuan untuk konteks laporan & evaluasi Wali Kelas
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

        // Komputasi metadata kesiapan, status alur kerja, kelengkapan mapel, & presensi secara bulk (bebas N+1)
        [$santriReportMeta, $workflowSummary, $archivedRapors] = $this->buildSantriWorkspaceMeta($santris, $selectedYear);

        $historyLogs = AdminActivityLog::query()
            ->whereIn('action', ['approve', 'publish', 'validate', 'revision', 'export', 'update'])
            ->where(function ($q) {
                $q->whereIn('target_type', ['RaporSemester', 'Kelas', 'Santri'])
                    ->orWhere('description', 'like', '%rapor%')
                    ->orWhere('description', 'like', '%Rapor%')
                    ->orWhere('description', 'like', '%Leger%')
                    ->orWhere('description', 'like', '%Presensi%');
            })
            ->orderByDesc('id')
            ->limit(25)
            ->get();

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
            'subjects',
            'activeTab',
            'santriReportMeta',
            'workflowSummary',
            'archivedRapors',
            'historyLogs'
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
            'save_mode' => 'nullable|in:draft,validate',
            'tab' => 'nullable|in:laporan,kelola,persetujuan,riwayat',
        ]);

        $allowFiltered = $request->boolean('allow_filtered');
        $hasExplicitMode = $request->filled('save_mode');
        $saveMode = $request->input('save_mode', 'draft');
        $explicitTaId = $request->filled('tahun_ajaran_id') ? (int) $request->input('tahun_ajaran_id') : null;

        $savedCount = 0;
        $validatedCount = 0;
        $skippedLockedCount = 0;

        foreach ($validated['evaluasi'] as $santriId => $data) {
            $santri = Santri::with(['kelas', 'raporSemesters'])->find($santriId);
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
            $targetTaId = $explicitTaId ?? $targetKelas->tahun_ajaran_id ?? $kelas->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;

            if (! $targetTaId) {
                continue;
            }

            $existing = $santri->raporSemesters->firstWhere('tahun_ajaran_id', $targetTaId);
            if ($hasExplicitMode && $existing && $existing->isFinalLocked()) {
                $skippedLockedCount++;

                continue;
            }

            $sikapVal = filled($data['nilai_sikap'] ?? null) ? $data['nilai_sikap'] : null;
            $catatanVal = filled($data['catatan_wali_kelas'] ?? null) ? trim($data['catatan_wali_kelas']) : null;

            if ($hasExplicitMode) {
                $nextWorkflowStage = ($existing && $existing->workflow_stage === 'revision') ? 'revision' : 'draft';
                $attributes = [
                    'kelas_id' => $targetKelas->id,
                    'nilai_sikap' => $sikapVal,
                    'catatan_wali_kelas' => $catatanVal,
                    'status_publikasi' => 'draft',
                    'workflow_stage' => $nextWorkflowStage,
                ];

                $record = RaporSemester::updateOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'tahun_ajaran_id' => $targetTaId,
                    ],
                    $attributes
                );

                if ($saveMode === 'validate') {
                    $readiness = $this->assessSantriRaporReadiness($santri, $targetTaId, $record);
                    if ($readiness['ready']) {
                        $record->update([
                            'workflow_stage' => 'validated',
                            'validated_by' => Auth::id(),
                            'validated_at' => now(),
                        ]);
                        $validatedCount++;
                    }
                }
            } else {
                // Kompatibilitas pemanggilan tanpa save_mode
                RaporSemester::updateOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'tahun_ajaran_id' => $targetTaId,
                    ],
                    [
                        'kelas_id' => $targetKelas->id,
                        'nilai_sikap' => $sikapVal,
                        'catatan_wali_kelas' => $catatanVal,
                        'status_publikasi' => 'published',
                    ]
                );
            }

            $savedCount++;
        }

        AdminActivityLog::log('update', "Memperbarui nilai sikap & catatan wali kelas rapor ({$kelas->nama_kelas}, {$savedCount} santri)", 'Kelas', $kelas->id);

        $message = $saveMode === 'validate'
            ? "Perubahan disimpan ({$savedCount} santri) dan {$validatedCount} rapor siap disetujui."
            : 'Perubahan draft penilaian sikap & catatan wali kelas berhasil disimpan.';

        if ($skippedLockedCount > 0) {
            $message .= " ({$skippedLockedCount} rapor yang sudah disetujui dilewati karena terkunci.)";
        }

        return redirect()->route('admin.reports.index', array_filter([
            'tahun_ajaran_id' => $request->input('tahun_ajaran_id'),
            'tingkat' => $request->input('tingkat'),
            'filter_kelas_id' => $request->input('filter_kelas_id', $kelas->id),
            'search' => $request->input('search'),
            'tab' => $request->input('tab', $hasExplicitMode ? 'kelola' : null),
        ]))->with('success', $message);
    }

    /**
     * Simpan evaluasi satu santri melalui Side Drawer (mendukung Draft maupun Simpan & Validasi).
     */
    public function updateSantriEvaluation(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'nilai_sikap' => 'nullable|string|max:50',
            'catatan_wali_kelas' => 'nullable|string|max:1000',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
            'save_mode' => 'nullable|in:draft,validate',
            'tab' => 'nullable|in:laporan,kelola,persetujuan,riwayat',
        ]);

        $santri->loadMissing(['kelas', 'raporSemesters']);
        $tahunAjaran = $request->filled('tahun_ajaran_id')
            ? TahunAjaran::findOrFail((int) $request->input('tahun_ajaran_id'))
            : (TahunAjaran::getAktif() ?? $santri->kelas?->tahunAjaran);

        $kelasAcuan = $this->raporService->resolveKelasForSantriInYear($santri, $tahunAjaran) ?? $santri->kelas;
        if ($kelasAcuan) {
            $this->authorizeClassAccess($kelasAcuan);
        }

        $targetTaId = $tahunAjaran?->id ?? $kelasAcuan?->tahun_ajaran_id;
        if (! $targetTaId) {
            return $this->respondEvaluationError($request, 'Periode Tahun Ajaran tidak ditemukan untuk santri ini.');
        }

        $existing = $santri->raporSemesters->firstWhere('tahun_ajaran_id', $targetTaId);
        if ($existing && $existing->isFinalLocked()) {
            return $this->respondEvaluationError(
                $request,
                "Rapor {$santri->nama_lengkap} telah disetujui dan terkunci. Gunakan prosedur Buka Revisi Resmi apabila diperlukan perbaikan."
            );
        }

        $sikapVal = filled($validated['nilai_sikap'] ?? null) ? $validated['nilai_sikap'] : null;
        $catatanVal = filled($validated['catatan_wali_kelas'] ?? null) ? trim($validated['catatan_wali_kelas']) : null;
        $saveMode = $validated['save_mode'] ?? 'draft';
        $nextStage = ($existing && $existing->workflow_stage === 'revision') ? 'revision' : 'draft';

        $record = RaporSemester::updateOrCreate(
            [
                'santri_id' => $santri->id,
                'tahun_ajaran_id' => $targetTaId,
            ],
            [
                'kelas_id' => $kelasAcuan?->id ?? $santri->kelas_id,
                'nilai_sikap' => $sikapVal,
                'catatan_wali_kelas' => $catatanVal,
                'status_publikasi' => 'draft',
                'workflow_stage' => $nextStage,
            ]
        );

        $readiness = $this->assessSantriRaporReadiness($santri, $targetTaId, $record);
        $warningMessage = null;

        if ($saveMode === 'validate') {
            if ($readiness['ready']) {
                $record->update([
                    'workflow_stage' => 'validated',
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                ]);
                AdminActivityLog::log('validate', "Memvalidasi kesiapan rapor santri: {$santri->nama_lengkap} ({$santri->nis})", 'RaporSemester', $santri->id);
            } else {
                $warningMessage = 'Draft evaluasi tersimpan, namun rapor belum dapat divalidasi: '.implode(', ', $readiness['missing']).'.';
                AdminActivityLog::log('update', "Menyimpan draft evaluasi rapor santri: {$santri->nama_lengkap} ({$santri->nis})", 'RaporSemester', $santri->id);
            }
        } else {
            AdminActivityLog::log('update', "Menyimpan draft evaluasi rapor santri: {$santri->nama_lengkap} ({$santri->nis})", 'RaporSemester', $santri->id);
        }

        $successMessage = $warningMessage
            ?? ($saveMode === 'validate'
                ? "Evaluasi {$santri->nama_lengkap} berhasil disimpan & divalidasi (Siap Disetujui)."
                : "Draft evaluasi {$santri->nama_lengkap} berhasil disimpan.");

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'validated' => $record->workflow_stage === 'validated',
                'warning' => $warningMessage !== null,
                'message' => $successMessage,
                'santri_id' => $santri->id,
                'nilai_sikap' => $record->nilai_sikap,
                'catatan_wali_kelas' => $record->catatan_wali_kelas,
                'workflow_stage' => $record->workflow_stage,
                'missing_items' => $readiness['missing'],
            ]);
        }

        $redirect = redirect()->route('admin.reports.index', array_filter([
            'tahun_ajaran_id' => $targetTaId,
            'tingkat' => $request->input('tingkat'),
            'filter_kelas_id' => $request->input('filter_kelas_id'),
            'search' => $request->input('search'),
            'tab' => $request->input('tab', 'kelola'),
        ]));

        return $warningMessage
            ? $redirect->with('error', $warningMessage)
            : $redirect->with('success', $successMessage);
    }

    /**
     * Validasi kelengkapan rapor (Tahap 2: Draft -> Validasi / Siap Disetujui)
     */
    public function validateRapor(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => 'nullable|exists:santris,id',
            'kelas_id' => 'nullable|exists:kelasses,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
            'tab' => 'nullable|in:laporan,kelola,persetujuan,riwayat',
        ]);

        $activeYear = TahunAjaran::getAktif();
        $targetTaId = $request->filled('tahun_ajaran_id')
            ? (int) $request->input('tahun_ajaran_id')
            : $activeYear?->id;

        if (! empty($validated['santri_id'])) {
            $santri = Santri::with(['kelas', 'raporSemesters'])->findOrFail($validated['santri_id']);
            if ($santri->kelas) {
                $this->authorizeClassAccess($santri->kelas);
            }

            $taId = $targetTaId ?? $santri->kelas?->tahun_ajaran_id;
            $existing = $santri->raporSemesters->firstWhere('tahun_ajaran_id', $taId);
            if ($existing && $existing->isFinalLocked()) {
                return redirect()->back()->with('error', "Rapor {$santri->nama_lengkap} sudah disetujui dan terkunci.");
            }

            $check = $this->assessSantriRaporReadiness($santri, $taId, $existing);
            if (! $check['ready']) {
                return redirect()->back()->with('error', "Rapor {$santri->nama_lengkap} belum dapat divalidasi: ".implode(', ', $check['missing']).'.');
            }

            RaporSemester::updateOrCreate(
                ['santri_id' => $santri->id, 'tahun_ajaran_id' => $taId],
                [
                    'kelas_id' => $check['kelas_id'] ?? $santri->kelas_id,
                    'status_publikasi' => 'draft',
                    'workflow_stage' => 'validated',
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                ]
            );

            AdminActivityLog::log('validate', "Memvalidasi kesiapan rapor santri: {$santri->nama_lengkap} ({$santri->nis})", 'RaporSemester', $santri->id);

            return redirect()->back()->with('success', "Rapor {$santri->nama_lengkap} lolos validasi dan siap disetujui.");
        }

        if (! empty($validated['kelas_id'])) {
            $kelas = Kelas::findOrFail($validated['kelas_id']);
            $this->authorizeClassAccess($kelas);
            $taObj = $targetTaId ? TahunAjaran::find($targetTaId) : ($kelas->tahunAjaran ?? $activeYear);
            $taId = $taObj?->id;
            $santrisInClass = $this->resolveSantrisForClassReport($kelas, $taObj);

            $validatedCount = 0;
            $incompleteCount = 0;

            foreach ($santrisInClass as $santri) {
                $existing = $santri->raporSemesters->firstWhere('tahun_ajaran_id', $taId);
                if ($existing && $existing->isFinalLocked()) {
                    continue;
                }

                $check = $this->assessSantriRaporReadiness($santri, $taId, $existing);
                if ($check['ready'] && $taId) {
                    RaporSemester::updateOrCreate(
                        ['santri_id' => $santri->id, 'tahun_ajaran_id' => $taId],
                        [
                            'kelas_id' => $kelas->id,
                            'status_publikasi' => 'draft',
                            'workflow_stage' => 'validated',
                            'validated_by' => Auth::id(),
                            'validated_at' => now(),
                        ]
                    );
                    $validatedCount++;
                } else {
                    $incompleteCount++;
                }
            }

            if ($validatedCount === 0) {
                return redirect()->back()->with('error', "Belum ada rapor di {$kelas->nama_kelas} yang memenuhi seluruh syarat validasi ({$incompleteCount} santri belum lengkap).");
            }

            AdminActivityLog::log('validate', "Memvalidasi kolektif kesiapan rapor {$kelas->nama_kelas} ({$validatedCount} santri siap disetujui)", 'Kelas', $kelas->id);

            $msg = "{$validatedCount} rapor di {$kelas->nama_kelas} berhasil divalidasi dan siap disetujui.";
            if ($incompleteCount > 0) {
                $msg .= " ({$incompleteCount} santri belum lengkap dan tetap berstatus draft.)";
            }

            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with('error', 'Pilih santri atau kelas yang akan divalidasi.');
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
            'raporSemesters.approvedBy',
            'raporSemesters.publishedBy',
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
            'raporSemesters.approvedBy',
            'raporSemesters.publishedBy',
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

        $raporSem = $raporData['raporSemester'] ?? null;
        $verSuffix = ($raporSem && $raporSem->isFinalLocked())
            ? '_v'.($raporSem->version ?? 1)
            : '_Draft';
        $fileName = 'Rapor_'.Str::slug($santri->nama_lengkap).'_'.($santri->nis).$verSuffix.'.pdf';

        return $pdf->download($fileName);
    }

    private function resolveSantrisForClassReport(Kelas $kelas, ?TahunAjaran $tahunAjaran)
    {
        $taId = $tahunAjaran?->id;

        return Santri::with([
            'kelas.waliKelas',
            'kelas.tahunAjaran',
            'raporSemesters.approvedBy',
            'raporSemesters.publishedBy',
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
                    ->orWhereHas('riwayatKelas', function ($rq) use ($kelas, $tahunAjaran) {
                        $rq->where('kelas_lama_id', $kelas->id);
                        if ($tahunAjaran) {
                            $rq->whereHas('kelasLama', fn ($kq) => $kq->where('tahun_ajaran_id', $tahunAjaran->id));
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

        $santri->loadMissing(['kelas', 'raporSemesters']);
        $activeYear = TahunAjaran::getAktif();
        $targetTaId = $request->filled('tahun_ajaran_id')
            ? (int) $request->input('tahun_ajaran_id')
            : ($santri->kelas?->tahun_ajaran_id ?? $activeYear?->id);

        $existing = $targetTaId ? $santri->raporSemesters->firstWhere('tahun_ajaran_id', $targetTaId) : null;

        if ($request->boolean('enforce_validation')) {
            $check = $this->assessSantriRaporReadiness($santri, $targetTaId, $existing);
            if (! $check['ready']) {
                return redirect()->back()->with(
                    'error',
                    "Rapor {$santri->nama_lengkap} belum memenuhi syarat persetujuan: ".implode(', ', $check['missing']).'.'
                );
            }
        }

        $autoPublish = $request->boolean('auto_publish');
        $newVersion = 1;

        if ($targetTaId) {
            $wasRevision = $existing && ($existing->workflow_stage === 'revision' || ($existing->approved_at !== null && ! $existing->isFinalLocked()));
            $newVersion = $existing
                ? ($wasRevision ? ((int) ($existing->version ?? 1) + 1) : max(1, (int) ($existing->version ?? 1)))
                : 1;

            $attributes = [
                'kelas_id' => $existing?->kelas_id ?? $santri->kelas_id,
                'status_publikasi' => $autoPublish ? 'published' : 'approved',
                'workflow_stage' => $autoPublish ? 'published' : 'approved',
                'version' => $newVersion,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ];

            if ($autoPublish) {
                $attributes['published_by'] = $user->id;
                $attributes['published_at'] = now();
            }

            RaporSemester::updateOrCreate(
                [
                    'santri_id' => $santri->id,
                    'tahun_ajaran_id' => $targetTaId,
                ],
                $attributes
            );
        }

        $actionLabel = $autoPublish ? 'Menyetujui & menerbitkan' : 'Menyetujui (approve)';
        AdminActivityLog::log('approve', "{$actionLabel} rapor santri: {$santri->nama_lengkap} ({$santri->nis}) — Versi v{$newVersion}.0", 'RaporSemester', $santri->id);

        $statusLabel = $autoPublish ? "disetujui & diterbitkan (Versi v{$newVersion}.0)" : "disetujui (Approved · Versi v{$newVersion}.0)";

        return redirect()->back()->with('success', "Rapor santri {$santri->nama_lengkap} berhasil {$statusLabel}. Nilai dan presensi kini terkunci.");
    }

    public function approveClass(Request $request, Kelas $kelas)
    {
        $user = Auth::user();
        if (! $user->isKepalaSekolah() && ! $user->isPimpinan() && ! $user->isSuperAdmin()) {
            abort(403, 'Hanya Kepala Sekolah atau Pimpinan yang berwenang menyetujui (approve) rapor kelas.');
        }

        $activeYear = TahunAjaran::getAktif();
        $targetTaId = $request->filled('tahun_ajaran_id')
            ? (int) $request->input('tahun_ajaran_id')
            : ($kelas->tahun_ajaran_id ?? $activeYear?->id);

        $enforceValidation = $request->boolean('enforce_validation');
        $autoPublish = $request->boolean('auto_publish');

        $taObj = $targetTaId ? TahunAjaran::find($targetTaId) : null;
        $santris = $enforceValidation
            ? $this->resolveSantrisForClassReport($kelas, $taObj)
            : $kelas->load('santris.raporSemesters')->santris;

        $count = 0;
        $skippedIncomplete = 0;

        foreach ($santris as $santri) {
            if (! $targetTaId) {
                continue;
            }

            $existing = $santri->relationLoaded('raporSemesters')
                ? $santri->raporSemesters->firstWhere('tahun_ajaran_id', $targetTaId)
                : RaporSemester::where('santri_id', $santri->id)->where('tahun_ajaran_id', $targetTaId)->first();

            if ($enforceValidation) {
                if ($existing && $existing->isFinalLocked()) {
                    continue;
                }
                $check = $this->assessSantriRaporReadiness($santri, $targetTaId, $existing);
                if (! $check['ready']) {
                    $skippedIncomplete++;

                    continue;
                }
            }

            $wasRevision = $existing && ($existing->workflow_stage === 'revision' || ($existing->approved_at !== null && ! $existing->isFinalLocked()));
            $newVersion = $existing
                ? ($wasRevision ? ((int) ($existing->version ?? 1) + 1) : max(1, (int) ($existing->version ?? 1)))
                : 1;

            $attributes = [
                'kelas_id' => $kelas->id,
                'status_publikasi' => $autoPublish ? 'published' : 'approved',
                'workflow_stage' => $autoPublish ? 'published' : 'approved',
                'version' => $newVersion,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ];

            if ($autoPublish) {
                $attributes['published_by'] = $user->id;
                $attributes['published_at'] = now();
            }

            RaporSemester::updateOrCreate(
                [
                    'santri_id' => $santri->id,
                    'tahun_ajaran_id' => $targetTaId,
                ],
                $attributes
            );
            $count++;
        }

        if ($enforceValidation && $count === 0) {
            return redirect()->back()->with(
                'error',
                "Tidak ada rapor di {$kelas->nama_kelas} yang memenuhi syarat persetujuan ({$skippedIncomplete} santri belum lengkap nilai/evaluasinya)."
            );
        }

        AdminActivityLog::log('approve', "Menyetujui (approve) rapor kelas: {$kelas->nama_kelas} ({$count} santri)", 'Kelas', $kelas->id);

        $msg = "Rapor kelas {$kelas->nama_kelas} ({$count} santri) berhasil disetujui. Nilai dan presensi kini terkunci.";
        if ($skippedIncomplete > 0) {
            $msg .= " ({$skippedIncomplete} santri dilewati karena data belum lengkap.)";
        }

        return redirect()->back()->with('success', $msg);
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
            $santri = Santri::with(['kelas', 'raporSemesters'])->findOrFail($validated['santri_id']);

            return $this->approveSantri($request, $santri);
        }

        if (! empty($validated['kelas_id'])) {
            $kelas = Kelas::findOrFail($validated['kelas_id']);

            return $this->approveClass($request, $kelas);
        }

        return redirect()->back()->with('error', 'Silakan tentukan kelas atau santri yang ingin disetujui.');
    }

    /**
     * Tahap 4: Penerbitan Rapor Resmi yang sudah disetujui (Approved -> Terbit)
     */
    public function publishRapor(Request $request)
    {
        $user = Auth::user();
        if (! $user->isKepalaSekolah() && ! $user->isPimpinan() && ! $user->isSuperAdmin()) {
            abort(403, 'Hanya Kepala Sekolah atau Pimpinan yang berwenang menerbitkan rapor resmi.');
        }

        $validated = $request->validate([
            'santri_id' => 'nullable|exists:santris,id',
            'kelas_id' => 'nullable|exists:kelasses,id',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        $activeYear = TahunAjaran::getAktif();
        $targetTaId = $request->filled('tahun_ajaran_id')
            ? (int) $request->input('tahun_ajaran_id')
            : $activeYear?->id;

        if (! empty($validated['santri_id'])) {
            $santri = Santri::with(['kelas', 'raporSemesters'])->findOrFail($validated['santri_id']);
            $taId = $targetTaId ?? $santri->kelas?->tahun_ajaran_id;
            $rapor = $santri->raporSemesters->firstWhere('tahun_ajaran_id', $taId);

            if (! $rapor || ! $rapor->isFinalLocked()) {
                return redirect()->back()->with('error', "Rapor {$santri->nama_lengkap} harus disetujui terlebih dahulu sebelum diterbitkan.");
            }

            $rapor->update([
                'status_publikasi' => 'published',
                'workflow_stage' => 'published',
                'published_by' => $user->id,
                'published_at' => now(),
            ]);

            AdminActivityLog::log('publish', "Menerbitkan dokumen rapor resmi: {$santri->nama_lengkap} ({$santri->nis}) — Versi v{$rapor->version}.0", 'RaporSemester', $santri->id);

            return redirect()->back()->with('success', "Rapor resmi {$santri->nama_lengkap} (Versi v{$rapor->version}.0) berhasil diterbitkan.");
        }

        if (! empty($validated['kelas_id'])) {
            $kelas = Kelas::findOrFail($validated['kelas_id']);
            $taId = $targetTaId ?? $kelas->tahun_ajaran_id;

            $approvedRapors = RaporSemester::where('kelas_id', $kelas->id)
                ->when($taId, fn ($q) => $q->where('tahun_ajaran_id', $taId))
                ->where(function ($q) {
                    $q->where('status_publikasi', 'approved')
                        ->orWhere('workflow_stage', 'approved');
                })
                ->get();

            if ($approvedRapors->isEmpty()) {
                return redirect()->back()->with('error', "Tidak ada rapor berstatus Disetujui di {$kelas->nama_kelas} yang siap diterbitkan.");
            }

            foreach ($approvedRapors as $rapor) {
                $rapor->update([
                    'status_publikasi' => 'published',
                    'workflow_stage' => 'published',
                    'published_by' => $user->id,
                    'published_at' => now(),
                ]);
            }

            AdminActivityLog::log('publish', "Menerbitkan kolektif dokumen rapor resmi {$kelas->nama_kelas} ({$approvedRapors->count()} santri)", 'Kelas', $kelas->id);

            return redirect()->back()->with('success', "{$approvedRapors->count()} rapor di {$kelas->nama_kelas} berhasil diterbitkan secara resmi.");
        }

        return redirect()->back()->with('error', 'Pilih santri atau kelas yang akan diterbitkan.');
    }

    /**
     * Prosedur Revisi Resmi untuk rapor yang sudah disetujui / diterbitkan.
     */
    public function requestRevision(Request $request, Santri $santri)
    {
        $user = Auth::user();
        if (! $user->isKepalaSekolah() && ! $user->isPimpinan() && ! $user->isSuperAdmin()) {
            abort(403, 'Hanya Kepala Sekolah atau Pimpinan yang berwenang membuka revisi resmi dokumen rapor.');
        }

        $validated = $request->validate([
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
            'revision_note' => 'required|string|min:5|max:1000',
        ], [
            'revision_note.required' => 'Alasan atau catatan revisi resmi wajib diisi.',
            'revision_note.min' => 'Catatan revisi minimal 5 karakter agar dapat diaudit.',
        ]);

        $santri->loadMissing(['kelas', 'raporSemesters']);
        $targetTaId = $request->filled('tahun_ajaran_id')
            ? (int) $request->input('tahun_ajaran_id')
            : ($santri->kelas?->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id);

        $rapor = $targetTaId ? $santri->raporSemesters->firstWhere('tahun_ajaran_id', $targetTaId) : null;
        if (! $rapor || ! $rapor->isFinalLocked()) {
            return redirect()->back()->with('error', "Rapor {$santri->nama_lengkap} belum terkunci sehingga dapat langsung diedit tanpa prosedur revisi.");
        }

        $note = trim($validated['revision_note']);
        $currentVer = $rapor->version ?? 1;

        $rapor->update([
            'status_publikasi' => 'draft',
            'workflow_stage' => 'revision',
            'revision_note' => $note,
            'revision_requested_by' => $user->id,
            'revision_requested_at' => now(),
        ]);

        AdminActivityLog::log(
            'revision',
            "Membuka revisi resmi rapor {$santri->nama_lengkap} ({$santri->nis}, v{$currentVer}.0 -> revisi v".($currentVer + 1).".0): {$note}",
            'RaporSemester',
            $santri->id
        );

        return redirect()->back()->with(
            'success',
            "Prosedur revisi resmi dibuka untuk rapor {$santri->nama_lengkap}. Setelah diperbaiki dan disetujui ulang, dokumen akan terbit sebagai Versi v".($currentVer + 1).'.0.'
        );
    }

    /**
     * Komputasi metadata alur rapor seluruh santri pada workspace secara bulk (bebas N+1 query).
     */
    private function buildSantriWorkspaceMeta($santris, ?TahunAjaran $selectedYear): array
    {
        $summary = [
            'total' => $santris->count(),
            'draft' => 0,
            'incomplete' => 0,
            'ready_review' => 0,
            'ready_approve' => 0,
            'approved' => 0,
            'published' => 0,
            'revision' => 0,
        ];

        if ($santris->isEmpty()) {
            return [[], $summary, collect()];
        }

        $taId = $selectedYear?->id;
        $santriIds = $santris->pluck('id');

        // Resolusi kelas periode per santri menggunakan relasi yang telah di-eager-load
        $resolvedKelasBySantri = [];
        $relevantKelasIds = collect();

        foreach ($santris as $s) {
            $k = $s->kelas;
            if ($taId && (! $k || (int) $k->tahun_ajaran_id !== (int) $taId)) {
                $histNew = $s->riwayatKelas
                    ->where('tahun_ajaran_id', $taId)
                    ->whereNotNull('kelas_baru_id')
                    ->sortByDesc('id')
                    ->first();
                if ($histNew && $histNew->kelasBaru) {
                    $k = $histNew->kelasBaru;
                } else {
                    $histOld = $s->riwayatKelas
                        ->filter(fn ($r) => $r->kelasLama && (int) $r->kelasLama->tahun_ajaran_id === (int) $taId)
                        ->sortByDesc('id')
                        ->first();
                    if ($histOld && $histOld->kelasLama) {
                        $k = $histOld->kelasLama;
                    }
                }
            }
            $resolvedKelasBySantri[$s->id] = $k;
            if ($k) {
                $relevantKelasIds->push($k->id);
            }
        }

        $relevantKelasIds = $relevantKelasIds->unique()->values();

        // 1. Jumlah mata pelajaran terjadwal per kelas
        $scheduledMapelByKelas = $relevantKelasIds->isNotEmpty()
            ? JadwalPelajaran::whereIn('kelas_id', $relevantKelasIds)
                ->when($taId, fn ($q) => $q->where('tahun_ajaran_id', $taId))
                ->select('kelas_id', 'mata_pelajaran_id')
                ->get()
                ->groupBy('kelas_id')
                ->map(fn ($rows) => $rows->pluck('mata_pelajaran_id')->filter()->unique()->count())
            : collect();

        // 2. Capaian nilai per santri pada periode terpilih
        $gradesBySantri = Nilai::with('jadwalPelajaran:id,kelas_id,tahun_ajaran_id,mata_pelajaran_id')
            ->whereIn('santri_id', $santriIds)
            ->whereNotNull('nilai_akhir')
            ->when($taId, function ($q) use ($taId) {
                $q->whereHas('jadwalPelajaran', fn ($jq) => $jq->where('tahun_ajaran_id', $taId));
            })
            ->get()
            ->groupBy('santri_id');

        // 3. Ringkasan presensi per santri pada periode terpilih
        $attendanceRows = Absensi::query()
            ->whereIn('santri_id', $santriIds)
            ->when($taId, function ($q) use ($taId) {
                $q->whereHas('jadwalPelajaran', fn ($jq) => $jq->where('tahun_ajaran_id', $taId));
            })
            ->selectRaw('santri_id, status_kehadiran, COUNT(*) as total')
            ->groupBy('santri_id', 'status_kehadiran')
            ->get()
            ->groupBy('santri_id');

        $meta = [];
        $archived = collect();

        foreach ($santris as $s) {
            $kelasPeriode = $resolvedKelasBySantri[$s->id] ?? $s->kelas;
            $effectiveTaId = $taId ?? $kelasPeriode?->tahun_ajaran_id;
            $rSem = $s->raporSemesterForYear($effectiveTaId);

            $scheduledCount = $kelasPeriode ? (int) ($scheduledMapelByKelas->get($kelasPeriode->id, 0)) : 0;
            $sGrades = $gradesBySantri->get($s->id, collect());
            $gradedMapelCount = $sGrades
                ->map(fn ($n) => $n->jadwalPelajaran?->mata_pelajaran_id ?? ('j-'.$n->jadwal_pelajaran_id))
                ->unique()
                ->count();
            $avgScore = $sGrades->isNotEmpty() ? round((float) $sGrades->avg('nilai_akhir'), 2) : null;

            $sAbs = $attendanceRows->get($s->id, collect())->keyBy('status_kehadiran');
            $hadir = (int) ($sAbs->get('Hadir')?->total ?? 0);
            $izin = (int) ($sAbs->get('Izin')?->total ?? 0);
            $sakit = (int) ($sAbs->get('Sakit')?->total ?? 0);
            $alpa = (int) ($sAbs->get('Alpa')?->total ?? 0);

            $sikap = $rSem?->nilai_sikap;
            $catatan = $rSem?->catatan_wali_kelas;

            $missing = [];
            if ($scheduledCount === 0 && $gradedMapelCount === 0) {
                $missing[] = 'Nilai akademik belum tersedia';
            } elseif ($scheduledCount > 0 && $gradedMapelCount < $scheduledCount) {
                $missing[] = ($scheduledCount - $gradedMapelCount).' mapel belum dinilai';
            }
            if (! filled($sikap)) {
                $missing[] = 'Predikat sikap belum dipilih';
            }
            if (! filled($catatan)) {
                $missing[] = 'Catatan wali kelas belum diisi';
            }

            $isComplete = empty($missing);
            $isLocked = $rSem ? $rSem->isFinalLocked() : false;
            $version = max(1, (int) ($rSem?->version ?? 1));

            if ($rSem && ($rSem->workflow_stage === 'published' || $rSem->published_at !== null) && $isLocked) {
                $statusKey = 'published';
                $statusLabel = 'Terbit';
                $summary['published']++;
            } elseif ($isLocked) {
                $statusKey = 'approved';
                $statusLabel = 'Disetujui';
                $summary['approved']++;
            } elseif ($rSem && $rSem->workflow_stage === 'revision') {
                $statusKey = 'revision';
                $statusLabel = 'Perlu Revisi';
                $summary['revision']++;
            } elseif ($isComplete && $rSem && ($rSem->workflow_stage === 'validated' || ($rSem->status_publikasi === 'published' && ! $isLocked))) {
                $statusKey = 'ready_approve';
                $statusLabel = 'Siap Disetujui';
                $summary['ready_approve']++;
            } elseif ($isComplete) {
                $statusKey = 'ready_review';
                $statusLabel = 'Siap Ditinjau';
                $summary['ready_review']++;
            } elseif ($rSem !== null || filled($sikap) || filled($catatan)) {
                $statusKey = 'incomplete';
                $statusLabel = 'Belum Lengkap';
                $summary['incomplete']++;
            } else {
                $statusKey = 'draft';
                $statusLabel = 'Draft';
                $summary['draft']++;
            }

            if ($rSem && ($isLocked || $rSem->workflow_stage === 'revision' || $version > 1)) {
                $archived->push($s);
            }

            $meta[$s->id] = [
                'kelas_periode' => $kelasPeriode,
                'kelas_nama' => $kelasPeriode?->nama_kelas ?? $s->kelas?->nama_kelas ?? '-',
                'rapor' => $rSem,
                'nilai_sikap' => $sikap,
                'catatan_wali_kelas' => $catatan,
                'scheduled_mapel_count' => $scheduledCount,
                'graded_mapel_count' => $gradedMapelCount,
                'avg_score' => $avgScore,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'missing_items' => $missing,
                'is_complete' => $isComplete,
                'is_locked' => $isLocked,
                'version' => $version,
                'status_key' => $statusKey,
                'status_label' => $statusLabel,
                'approved_by_name' => $rSem?->approvedBy?->name,
                'approved_at_label' => $rSem?->approved_at?->translatedFormat('d M Y H:i'),
                'published_by_name' => $rSem?->publishedBy?->name,
                'published_at_label' => $rSem?->published_at?->translatedFormat('d M Y H:i'),
                'revision_note' => $rSem?->revision_note,
                'revision_by_name' => $rSem?->revisionRequestedBy?->name,
                'revision_at_label' => $rSem?->revision_requested_at?->translatedFormat('d M Y H:i'),
            ];
        }

        return [$meta, $summary, $archived];
    }

    /**
     * Periksa kelengkapan komponen wajib rapor santri sebelum validasi / approval.
     */
    private function assessSantriRaporReadiness(Santri $santri, ?int $targetTaId, ?RaporSemester $existingRapor = null): array
    {
        $taObj = $targetTaId ? TahunAjaran::find($targetTaId) : null;
        $kelasPeriode = $this->raporService->resolveKelasForSantriInYear($santri, $taObj) ?? $santri->kelas;
        $rapor = $existingRapor ?? ($targetTaId
            ? RaporSemester::where('santri_id', $santri->id)->where('tahun_ajaran_id', $targetTaId)->first()
            : null);

        $scheduledCount = $kelasPeriode
            ? JadwalPelajaran::where('kelas_id', $kelasPeriode->id)
                ->when($targetTaId, fn ($q) => $q->where('tahun_ajaran_id', $targetTaId))
                ->distinct('mata_pelajaran_id')
                ->count('mata_pelajaran_id')
            : 0;

        $gradedCount = Nilai::where('santri_id', $santri->id)
            ->whereNotNull('nilai_akhir')
            ->when($targetTaId, function ($q) use ($targetTaId) {
                $q->whereHas('jadwalPelajaran', fn ($jq) => $jq->where('tahun_ajaran_id', $targetTaId));
            })
            ->with('jadwalPelajaran:id,mata_pelajaran_id')
            ->get()
            ->map(fn ($n) => $n->jadwalPelajaran?->mata_pelajaran_id ?? ('j-'.$n->jadwal_pelajaran_id))
            ->unique()
            ->count();

        $missing = [];
        if ($scheduledCount === 0 && $gradedCount === 0) {
            $missing[] = 'nilai akademik belum diinput';
        } elseif ($scheduledCount > 0 && $gradedCount < $scheduledCount) {
            $missing[] = ($scheduledCount - $gradedCount).' mata pelajaran belum dinilai';
        }

        if (! filled($rapor?->nilai_sikap)) {
            $missing[] = 'predikat sikap belum dipilih';
        }

        if (! filled($rapor?->catatan_wali_kelas)) {
            $missing[] = 'catatan wali kelas belum diisi';
        }

        return [
            'ready' => empty($missing),
            'missing' => $missing,
            'rapor' => $rapor,
            'kelas_id' => $kelasPeriode?->id ?? $santri->kelas_id,
        ];
    }

    private function respondEvaluationError(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->with('error', $message);
    }
}
