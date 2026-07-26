<?php

namespace App\Http\Controllers;

use App\Models\EducationProgram;
use App\Models\JadwalPelajaran;
use App\Models\News;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect Santri ke Portal Santri
        if ($user->isSantri()) {
            return redirect()->route('santri.dashboard');
        }

        // Redirect Pimpinan ke Dashboard Pimpinan
        if ($user->isPimpinan()) {
            return redirect()->route('pimpinan.dashboard');
        }

        // Data khusus Tenaga Pengajar (Ustaz / Ustazah)
        if ($user->isTenagaPengajar()) {
            $pengajar = $user->pengajar;
            $mySchedules = collect();
            $myClassesCount = 0;
            $mySubjectsCount = 0;
            $mySantriCount = 0;
            $myWaliKelas = null;

            if ($pengajar) {
                $mySchedules = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'tahunAjaran'])
                    ->where('pengajar_id', $pengajar->id)
                    ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                    ->orderBy('waktu_mulai')
                    ->get();

                $myClassesCount = $mySchedules->pluck('kelas_id')->unique()->filter()->count();
                $mySubjectsCount = $mySchedules->pluck('mata_pelajaran_id')->unique()->filter()->count();

                $classIds = $mySchedules->pluck('kelas_id')->unique()->filter();
                $mySantriCount = Santri::whereIn('kelas_id', $classIds)->where('status_santri', 'Aktif')->count();

                $myWaliKelas = $pengajar->kelasWali()->first();
            }

            return view('dashboard.index', compact(
                'pengajar',
                'mySchedules',
                'myClassesCount',
                'mySubjectsCount',
                'mySantriCount',
                'myWaliKelas'
            ));
        }

        // Data Admin / Pengurus Akademik / Super Admin
        $newRegistrantsCount = SantriRegistration::where('status', 'pending')->count();
        $totalSantri         = SantriRegistration::where('status', 'accepted')->count();
        $activeProgramsCount = EducationProgram::where('is_active', true)->count();
        $totalNews           = News::count();

        $pendingRegistrants = SantriRegistration::where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $adminUsers = User::query()
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'avatar']);

        return view('dashboard.index', compact(
            'newRegistrantsCount',
            'totalSantri',
            'activeProgramsCount',
            'totalNews',
            'pendingRegistrants',
            'adminUsers'
        ));
    }
}
