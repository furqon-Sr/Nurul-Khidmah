<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Nilai;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = JadwalPelajaran::with(['tahunAjaran', 'kelas', 'mataPelajaran', 'pengajar']);

        if ($user->isTenagaPengajar() && $user->pengajar) {
            $query->where('pengajar_id', $user->pengajar->id);
        }

        $schedules = $query->get();

        return view('teacher.grades.index', compact('schedules'));
    }

    public function edit(JadwalPelajaran $schedule)
    {
        $schedule->load(['kelas', 'mataPelajaran', 'pengajar', 'tahunAjaran']);

        $santris = Santri::where('kelas_id', $schedule->kelas_id)
                         ->where('status_santri', 'Aktif')
                         ->orderBy('nama_lengkap')
                         ->get();

        $existingNilai = Nilai::where('jadwal_pelajaran_id', $schedule->id)
                              ->get()
                              ->keyBy('santri_id');

        return view('teacher.grades.edit', compact('schedule', 'santris', 'existingNilai'));
    }

    public function update(Request $request, JadwalPelajaran $schedule)
    {
        $validated = $request->validate([
            'nilai' => 'required|array',
            'nilai.*.tugas' => 'nullable|numeric|min:0|max:100',
            'nilai.*.uts' => 'nullable|numeric|min:0|max:100',
            'nilai.*.uas' => 'nullable|numeric|min:0|max:100',
            'nilai.*.keterangan' => 'nullable|string',
        ]);

        foreach ($validated['nilai'] as $santriId => $data) {
            $tugas = floatval($data['tugas'] ?? 0);
            $uts = floatval($data['uts'] ?? 0);
            $uas = floatval($data['uas'] ?? 0);

            // Formula: Tugas 30%, UTS 30%, UAS 40%
            $nilaiAkhir = round(($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4), 2);
            $grade = Nilai::calculateGrade($nilaiAkhir);

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
                    'keterangan' => $data['keterangan'] ?? null,
                    'created_by' => Auth::id(),
                ]
            );
        }

        return redirect()->route('teacher.grades.index')->with('success', 'Pengolahan Nilai santri berhasil disimpan.');
    }
}
