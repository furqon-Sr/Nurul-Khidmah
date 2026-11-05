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

        $query = JadwalPelajaran::with(['tahunAjaran', 'kelas', 'mataPelajaran', 'pengajar', 'santris'])
            ->withCount('nilais');

        if ($activeYear && ! $request->filled('semua_periode')) {
            $query->where('tahun_ajaran_id', $activeYear->id);
        }

        if ($user->isTenagaPengajar()) {
            $pengajarId = $user->pengajar?->id ?? 0;
            $query->where('pengajar_id', $pengajarId);
        }

        $schedules = $query->orderByHari()->orderBy('waktu_mulai')->get();

        // Hitung jumlah santri aktif per kelas untuk indikator progres ketuntasan input nilai guru
        $activeSantriCounts = Santri::where('status_santri', 'Aktif')
            ->selectRaw('kelas_id, COUNT(*) as total')
            ->groupBy('kelas_id')
            ->pluck('total', 'kelas_id');

        return view('teacher.grades.index', compact('schedules', 'activeYear', 'activeSantriCounts'));
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
