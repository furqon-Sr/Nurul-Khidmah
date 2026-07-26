<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalPelajaran;
use App\Models\Nilai;
use App\Models\PageContent;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SantriPortalController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $santri = $user->santri;

        if (!$santri) {
            return view('santri.dashboard', [
                'santri' => null,
                'schedules' => collect(),
                'totalHadir' => 0,
                'totalIzin' => 0,
                'totalSakit' => 0,
                'totalAlpa' => 0,
                'gpa' => 0,
            ]);
        }

        $santri->load(['kelas', 'kelas.waliKelas']);

        // Fetch schedules for santri's class
        $schedules = JadwalPelajaran::with(['mataPelajaran', 'pengajar', 'tahunAjaran'])
            ->where('kelas_id', $santri->kelas_id)
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('waktu_mulai')
            ->get();

        // Attendance Stats
        $absensiQuery = Absensi::where('santri_id', $santri->id);
        $totalHadir = (clone $absensiQuery)->where('status_kehadiran', 'Hadir')->count();
        $totalIzin  = (clone $absensiQuery)->where('status_kehadiran', 'Izin')->count();
        $totalSakit = (clone $absensiQuery)->where('status_kehadiran', 'Sakit')->count();
        $totalAlpa  = (clone $absensiQuery)->where('status_kehadiran', 'Alpa')->count();

        // Nilai Stats
        $nilais = Nilai::where('santri_id', $santri->id)->get();
        $gpa = $nilais->isNotEmpty() ? round($nilais->avg('nilai_akhir'), 2) : 0;

        return view('santri.dashboard', compact(
            'santri',
            'schedules',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalAlpa',
            'gpa'
        ));
    }

    public function attendance()
    {
        $user = Auth::user();
        $santri = $user->santri;

        if (!$santri) {
            return redirect()->route('dashboard')->with('error', 'Data Santri tidak ditemukan.');
        }

        $absensiRecords = Absensi::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.pengajar'])
            ->where('santri_id', $santri->id)
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return view('santri.attendance', compact('santri', 'absensiRecords'));
    }

    public function grades()
    {
        $user = Auth::user();
        $santri = $user->santri;

        if (!$santri) {
            return redirect()->route('dashboard')->with('error', 'Data Santri tidak ditemukan.');
        }

        $santri->load(['kelas', 'kelas.tahunAjaran']);

        $nilais = Nilai::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.pengajar'])
            ->where('santri_id', $santri->id)
            ->get();

        $gpa = $nilais->isNotEmpty() ? round($nilais->avg('nilai_akhir'), 2) : 0;

        return view('santri.grades', compact('santri', 'nilais', 'gpa'));
    }

    public function downloadRaporPdf()
    {
        $user = Auth::user();
        $santri = $user->santri;

        if (!$santri) {
            return redirect()->back()->with('error', 'Data Santri tidak ditemukan.');
        }

        $santri->load(['kelas', 'kelas.waliKelas', 'kelas.tahunAjaran']);

        $tahunAjaran = TahunAjaran::where('status_aktif', true)->first() ?? $santri->kelas->tahunAjaran;

        $nilais = Nilai::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.pengajar'])
            ->where('santri_id', $santri->id)
            ->get();

        $absensiQuery = Absensi::where('santri_id', $santri->id);
        $totalHadir = (clone $absensiQuery)->where('status_kehadiran', 'Hadir')->count();
        $totalIzin  = (clone $absensiQuery)->where('status_kehadiran', 'Izin')->count();
        $totalSakit = (clone $absensiQuery)->where('status_kehadiran', 'Sakit')->count();
        $totalAlpa  = (clone $absensiQuery)->where('status_kehadiran', 'Alpa')->count();

        $siteInfo = PageContent::all()->keyBy('section_key');

        return view('santri.rapor-pdf', compact(
            'santri',
            'tahunAjaran',
            'nilais',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalAlpa',
            'siteInfo'
        ));
    }
}
