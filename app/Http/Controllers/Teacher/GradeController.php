<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\GradeComponent;
use App\Models\JadwalPelajaran;
use App\Models\Nilai;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeYear = TahunAjaran::getAktif();
        $academicYears = TahunAjaran::orderByDesc('id')->get();

        // Periode: 'semua' (atau parameter lama semua_periode), tahun ajaran tertentu, atau periode aktif.
        $periodParam = $request->input('tahun_ajaran_id', $request->filled('semua_periode') ? 'semua' : $activeYear?->id);
        $selectedYearId = $periodParam === 'semua' || $periodParam === null ? null : (int) $periodParam;

        $query = JadwalPelajaran::with(['tahunAjaran', 'kelas', 'mataPelajaran', 'pengajar']);

        if ($selectedYearId) {
            $query->where('tahun_ajaran_id', $selectedYearId);
        }

        $isTeacherView = $user->isTenagaPengajar();
        if ($isTeacherView) {
            $query->where('pengajar_id', $user->pengajar?->id ?? 0);
        }

        $schedules = $query->orderByHari()->orderBy('waktu_mulai')->get();

        // Santri aktif per kelas (daftar id, bukan sekadar jumlah) agar progres dihitung per santri.
        $activeByClass = Santri::where('status_santri', 'Aktif')
            ->get(['id', 'kelas_id'])
            ->groupBy('kelas_id')
            ->map(fn ($group) => $group->pluck('id')->all());

        $nilaiBySchedule = Nilai::whereIn('jadwal_pelajaran_id', $schedules->pluck('id'))
            ->get(['jadwal_pelajaran_id', 'santri_id'])
            ->groupBy('jadwal_pelajaran_id');

        $items = $schedules->map(function (JadwalPelajaran $schedule) use ($activeByClass, $nilaiBySchedule) {
            $activeIds = $activeByClass->get($schedule->kelas_id, []);
            $expected = count($activeIds);

            $rows = ($nilaiBySchedule->get($schedule->id) ?? collect())->whereIn('santri_id', $activeIds);

            // Santri dihitung "sudah dinilai" bila memiliki baris nilai pada jadwal ini. Skema saat ini menyimpan
            // komponen kosong sebagai 0 sehingga kelengkapan per komponen (Tugas/UTS/UAS) belum dapat dibedakan.
            $complete = $rows->pluck('santri_id')->unique()->count();

            $status = match (true) {
                $expected === 0 => 'no_students',
                $complete >= $expected => 'done',
                $complete > 0 => 'in_progress',
                default => 'not_started',
            };

            return [
                'id' => $schedule->id,
                'mapel' => $schedule->mataPelajaran->nama_mapel ?? '-',
                'kelas' => $schedule->kelas->nama_kelas ?? '-',
                'pengajar' => $schedule->pengajar->nama_lengkap ?? '-',
                'periode' => ($schedule->tahunAjaran->nama_tahun_ajaran ?? '').' · '.($schedule->tahunAjaran->semester ?? ''),
                'jadwal' => trim(($schedule->hari ?? '').' '.($schedule->waktu_mulai ? date('H:i', strtotime($schedule->waktu_mulai)) : '')),
                'expected' => $expected,
                'complete' => $complete,
                'pending' => max(0, $expected - $complete),
                'percent' => $expected > 0 ? (int) round($complete / $expected * 100) : null,
                'status' => $status,
                'url' => route('teacher.grades.edit', $schedule->id),
            ];
        })->values();

        $withStudents = $items->where('expected', '>', 0);
        $summary = [
            'schedules' => $items->count(),
            'pending_students' => $withStudents->sum('pending'),
            'complete_entries' => $withStudents->sum('complete'),
            'expected_entries' => $withStudents->sum('expected'),
            'needs_action' => $withStudents->whereIn('status', ['not_started', 'in_progress'])->count(),
            'without_students' => $items->where('status', 'no_students')->count(),
        ];

        return view('teacher.grades.index', [
            'items' => $items,
            'summary' => $summary,
            'activeYear' => $activeYear,
            'academicYears' => $academicYears,
            'selectedYearId' => $selectedYearId,
            'showTeacherName' => ! $isTeacherView,
            'canViewClasses' => $user->isSuperAdmin() || $user->isPengurusAkademik() || $user->isKepalaSekolah(),
        ]);
    }

    public function edit(JadwalPelajaran $schedule)
    {
        $this->authorizeScheduleAccess($schedule);

        return view('teacher.grades.edit', [
            'schedule' => $schedule,
            'jadwal' => $schedule,
        ]);
    }

    public function update(Request $request, JadwalPelajaran $schedule)
    {
        $this->authorizeScheduleAccess($schedule);
        $schedule->load(['kelas', 'mataPelajaran', 'nilais']);

        $validated = $request->validate([
            'nilai' => 'required|array',
            'nilai.*.tugas' => 'nullable|numeric|min:0|max:100',
            'nilai.*.uts' => 'nullable|numeric|min:0|max:100',
            'nilai.*.uas' => 'nullable|numeric|min:0|max:100',
            'nilai.*.keterangan' => 'nullable|string|max:500',
        ]);

        $unitId = $schedule->kelas?->educational_unit_id ?? $schedule->mataPelajaran?->educational_unit_id;
        $components = GradeComponent::getComponentsForUnit($unitId);

        $bobotTugas = (float) ($components->firstWhere(fn ($c) => strtolower($c->nama_komponen) === 'tugas')?->bobot_persen ?? 30);
        $bobotUts = (float) ($components->firstWhere(fn ($c) => strtolower($c->nama_komponen) === 'uts')?->bobot_persen ?? 30);
        $bobotUas = (float) ($components->firstWhere(fn ($c) => strtolower($c->nama_komponen) === 'uas')?->bobot_persen ?? 40);
        $totalBobot = ($bobotTugas + $bobotUts + $bobotUas) ?: 100;

        $savedCount = 0;

        foreach ($validated['nilai'] as $santriId => $data) {
            $hasAnyScore = ($data['tugas'] ?? '') !== '' || ($data['uts'] ?? '') !== '' || ($data['uas'] ?? '') !== '';
            $hasNote = filled($data['keterangan'] ?? null);

            if (! $hasAnyScore && ! $hasNote) {
                // Jangan membuat baris nilai palsu (0 / Grade E) bagi santri yang memang belum dinilai
                continue;
            }

            $nilai = $schedule->nilais->firstWhere('santri_id', (int) $santriId)
                ?? Nilai::where('jadwal_pelajaran_id', $schedule->id)->where('santri_id', $santriId)->first()
                ?? new Nilai(['jadwal_pelajaran_id' => $schedule->id, 'santri_id' => $santriId]);

            $nilai->setRelation('jadwalPelajaran', $schedule);

            // Validasi hak akses & status penguncian nilai via NilaiPolicy
            $this->authorize('update', $nilai);

            $tugas = floatval($data['tugas'] ?? 0);
            $uts = floatval($data['uts'] ?? 0);
            $uas = floatval($data['uas'] ?? 0);

            // Dinamisasi perhitungan nilai akhir berdasarkan grade_components
            $nilaiAkhir = round((($tugas * $bobotTugas) + ($uts * $bobotUts) + ($uas * $bobotUas)) / $totalBobot, 2);
            $grade = Nilai::calculateGrade($nilaiAkhir, $unitId);

            Nilai::updateOrCreate(
                [
                    'jadwal_pelajaran_id' => $schedule->id,
                    'santri_id' => $santriId,
                ],
                [
                    'nilai_tugas' => $tugas,
                    'nilai_uts' => $uts,
                    'nilai_uas' => $uas,
                    'nilai_akhir' => $nilaiAkhir,
                    'grade' => $grade,
                    'keterangan' => $hasNote ? trim($data['keterangan']) : null,
                    'created_by' => Auth::id(),
                ]
            );
            $savedCount++;
        }

        return redirect()->route('teacher.grades.index')->with('success', "Pengolahan nilai berhasil disimpan untuk {$savedCount} santri.");
    }

    private function authorizeScheduleAccess(JadwalPelajaran $schedule): void
    {
        $user = Auth::user();
        if ($user->isTenagaPengajar() && $schedule->pengajar_id !== ($user->pengajar?->id ?? 0)) {
            abort(403, 'Anda hanya dapat mengolah nilai pada jadwal mengajar Anda sendiri.');
        }
    }
}
