<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\PageContent;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class AcademicReportController extends Controller
{
    public function index()
    {
        $classes = Kelas::with(['waliKelas', 'tahunAjaran'])->orderBy('nama_kelas')->get();
        $academicYears = TahunAjaran::orderBy('nama_tahun_ajaran', 'desc')->get();
        $santris = Santri::with('kelas')->where('status_santri', 'Aktif')->orderBy('nama_lengkap')->get();

        return view('reports.index', compact('classes', 'academicYears', 'santris'));
    }

    public function exportAttendancePdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelasses,id',
        ]);

        $kelas = Kelas::with(['waliKelas', 'tahunAjaran'])->findOrFail($request->kelas_id);
        $santris = Santri::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();

        $absensiSummary = [];
        foreach ($santris as $santri) {
            $q = Absensi::where('santri_id', $santri->id);
            $absensiSummary[$santri->id] = [
                'hadir' => (clone $q)->where('status_kehadiran', 'Hadir')->count(),
                'izin' => (clone $q)->where('status_kehadiran', 'Izin')->count(),
                'sakit' => (clone $q)->where('status_kehadiran', 'Sakit')->count(),
                'alpa' => (clone $q)->where('status_kehadiran', 'Alpa')->count(),
            ];
        }

        $siteInfo = PageContent::all()->keyBy('section_key');

        return view('reports.attendance-pdf', compact('kelas', 'santris', 'absensiSummary', 'siteInfo'));
    }

    public function exportGradesPdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelasses,id',
        ]);

        $kelas = Kelas::with(['waliKelas', 'tahunAjaran'])->findOrFail($request->kelas_id);
        $santris = Santri::where('kelas_id', $kelas->id)->orderBy('nama_lengkap')->get();

        $gradesBySantri = [];
        foreach ($santris as $santri) {
            $gradesBySantri[$santri->id] = Nilai::with('jadwalPelajaran.mataPelajaran')
                ->where('santri_id', $santri->id)
                ->get();
        }

        $siteInfo = PageContent::all()->keyBy('section_key');

        return view('reports.grades-pdf', compact('kelas', 'santris', 'gradesBySantri', 'siteInfo'));
    }

    public function exportSantriRaporPdf(Santri $santri)
    {
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
