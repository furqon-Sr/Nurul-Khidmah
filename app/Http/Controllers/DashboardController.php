<?php

namespace App\Http\Controllers;

use App\Models\EducationProgram;
use App\Models\Facility;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\News;
use App\Models\PageContent;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect Wali Santri ke Portal Wali (FR-11)
        if ($user->isSantri()) {
            return redirect()->route('wali.dashboard');
        }

        // Redirect Pimpinan ke Dashboard Eksekutif Pimpinan (FR-13)
        if ($user->isPimpinan()) {
            return redirect()->route('pimpinan.dashboard');
        }

        $activeYear = TahunAjaran::getAktif();

        // 1. Data khusus Kepala Sekolah (Monitoring Kepatuhan Guru & Akademik)
        if ($user->isKepalaSekolah()) {
            $totalTeachers = Pengajar::where('status_pengajar', 'Aktif')->count();
            $totalClasses = Kelas::when($activeYear, fn ($q) => $q->where('tahun_ajaran_id', $activeYear->id))->count();
            $activeSantriCount = Santri::where('status_santri', 'Aktif')->count();

            $schedulesQuery = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'pengajar'])
                ->when($activeYear, fn ($q) => $q->where('tahun_ajaran_id', $activeYear->id));

            $totalSchedules = (clone $schedulesQuery)->count();
            $gradedSchedulesCount = (clone $schedulesQuery)->has('nilais')->count();
            $ungradedSchedules = (clone $schedulesQuery)->doesntHave('nilais')->take(6)->get();

            $complianceRate = $totalSchedules > 0 ? round(($gradedSchedulesCount / $totalSchedules) * 100) : 100;

            return view('dashboard.index', compact(
                'activeYear',
                'totalTeachers',
                'totalClasses',
                'activeSantriCount',
                'totalSchedules',
                'gradedSchedulesCount',
                'complianceRate',
                'ungradedSchedules'
            ));
        }

        // 2. Data khusus Tenaga Pengajar (Ustaz / Ustazah)
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
                    ->when($activeYear, fn ($q) => $q->where('tahun_ajaran_id', $activeYear->id))
                    ->orderByHari()
                    ->orderBy('waktu_mulai')
                    ->get();

                $myClassesCount = $mySchedules->pluck('kelas_id')->unique()->filter()->count();
                $mySubjectsCount = $mySchedules->pluck('mata_pelajaran_id')->unique()->filter()->count();

                $classIds = $mySchedules->pluck('kelas_id')->unique()->filter();
                $mySantriCount = Santri::whereIn('kelas_id', $classIds)->where('status_santri', 'Aktif')->count();

                $myWaliKelas = $pengajar->kelasWali()
                    ->when($activeYear, fn ($q) => $q->where('tahun_ajaran_id', $activeYear->id))
                    ->first() ?? $pengajar->kelasWali()->first();
            }

            return view('dashboard.index', compact(
                'pengajar',
                'activeYear',
                'mySchedules',
                'myClassesCount',
                'mySubjectsCount',
                'mySantriCount',
                'myWaliKelas'
            ));
        }

        // 3. Data Pengurus Akademik (Staf TU / Admin) & Super Admin (SIAKAD, PPDB, & Konten Website)
        $newRegistrantsCount = SantriRegistration::where('status', 'pending')->count();
        $totalSantri = SantriRegistration::where('status', 'accepted')->count();
        $activeSantriCount = Santri::where('status_santri', 'Aktif')->count();
        $activeProgramsCount = EducationProgram::where('is_active', true)->count();
        $totalNews = News::count();
        $totalFacilities = Facility::count();
        $totalFees = PageContent::query()
            ->where(function ($q) {
                $q->where('section_key', 'education_fee_pdf')
                    ->orWhere('section_key', 'like', 'education_fee_file_%');
            })
            ->whereNotNull('image_path')
            ->count();

        $pendingRegistrants = SantriRegistration::where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $adminUsers = User::query()
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'avatar']);

        return view('dashboard.index', compact(
            'activeYear',
            'newRegistrantsCount',
            'totalSantri',
            'activeSantriCount',
            'activeProgramsCount',
            'totalNews',
            'totalFacilities',
            'totalFees',
            'pendingRegistrants',
            'adminUsers'
        ));
    }
}
