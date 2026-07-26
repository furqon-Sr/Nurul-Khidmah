<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\TahunAjaran;

class PimpinanDashboardController extends Controller
{
    public function index()
    {
        $activeYear = TahunAjaran::where('status_aktif', true)->first();

        $totalSantri = Santri::where('status_santri', 'Aktif')->count();
        $totalPengajar = Pengajar::where('status_pengajar', 'Aktif')->count();
        $totalKelas = Kelas::count();

        // Attendance stats
        $totalAbsensi = Absensi::count();
        $totalHadir = Absensi::where('status_kehadiran', 'Hadir')->count();
        $attendancePercentage = $totalAbsensi > 0 ? round(($totalHadir / $totalAbsensi) * 100, 1) : 0;

        // Grade stats
        $averageGrade = round(Nilai::avg('nilai_akhir') ?? 0, 2);
        $gradeDistribution = [
            'A' => Nilai::where('grade', 'A')->count(),
            'B' => Nilai::where('grade', 'B')->count(),
            'C' => Nilai::where('grade', 'C')->count(),
            'D' => Nilai::where('grade', 'D')->count(),
            'E' => Nilai::where('grade', 'E')->count(),
        ];

        // Class summaries
        $classes = Kelas::with(['waliKelas', 'santris'])->get();

        return view('pimpinan.dashboard', compact(
            'activeYear',
            'totalSantri',
            'totalPengajar',
            'totalKelas',
            'attendancePercentage',
            'averageGrade',
            'gradeDistribution',
            'classes'
        ));
    }
}
