<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AdminActivityLog;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\PageContent;
use App\Models\Pengajar;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;

class PimpinanDashboardController extends Controller
{
    public function index()
    {
        $data = $this->buildExecutiveReportData();

        return view('pimpinan.dashboard', $data);
    }

    public function exportPdf()
    {
        $data = $this->buildExecutiveReportData();
        $data['siteInfo'] = PageContent::all()->keyBy('section_key');

        AdminActivityLog::log(
            'export',
            'Mengunduh Ringkasan Laporan Track Record Eksekutif Pimpinan (PDF)',
            'TahunAjaran',
            $data['activeYear']?->id
        );

        return view('pimpinan.laporan-pdf', $data);
    }

    /**
     * Menghimpun seluruh metrik Track Record Makro & Ketuntasan Input Nilai Rapor (FR-13)
     */
    private function buildExecutiveReportData(): array
    {
        $activeYear = TahunAjaran::getAktif();

        // 1. Statistik Santri, Alumni/Lulus, Pengajar, & Kelas
        $totalSantriAktif = Santri::where('status_santri', 'Aktif')->count();
        $totalSantriLulus = Santri::whereIn('status_santri', ['Lulus', 'Alumni'])->count();
        $totalSantriMutasi = Santri::whereIn('status_santri', ['Mutasi', 'Non-Aktif'])->count();
        $totalPengajar = Pengajar::where('status_pengajar', 'Aktif')->count();

        $classesQuery = Kelas::with(['waliKelas', 'tahunAjaran'])
            ->when($activeYear, fn ($q) => $q->where('tahun_ajaran_id', $activeYear->id));
        $classes = $classesQuery->orderBy('nama_kelas')->get();
        if ($classes->isEmpty()) {
            $classes = Kelas::with(['waliKelas', 'tahunAjaran'])->orderBy('nama_kelas')->get();
        }
        $totalKelas = $classes->count();

        // 2. Track Record PPDB Tahunan (Pendaftar vs Diterima vs Pending/Ditolak)
        $ppdbRaw = SantriRegistration::query()
            ->selectRaw("COALESCE(academic_year, 'Tidak Tercatat') as periode")
            ->selectRaw('COUNT(*) as total_pendaftar')
            ->selectRaw("SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as diterima")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as ditolak")
            ->groupBy('periode')
            ->orderBy('periode', 'asc')
            ->get();

        $ppdbTotals = [
            'pendaftar' => (int) $ppdbRaw->sum('total_pendaftar'),
            'diterima' => (int) $ppdbRaw->sum('diterima'),
            'pending' => (int) $ppdbRaw->sum('pending'),
            'ditolak' => (int) $ppdbRaw->sum('ditolak'),
        ];

        // 3. Kehadiran & Rata-Rata Nilai pada Semester Aktif
        $absensiScope = Absensi::query()->when($activeYear, function ($q) use ($activeYear) {
            $q->whereHas('jadwalPelajaran', fn ($jq) => $jq->where('tahun_ajaran_id', $activeYear->id));
        });
        $totalAbsensi = (clone $absensiScope)->count();
        $totalHadir = (clone $absensiScope)->where('status_kehadiran', 'Hadir')->count();
        $attendancePercentage = $totalAbsensi > 0 ? round(($totalHadir / $totalAbsensi) * 100, 1) : 0;

        $nilaiScope = Nilai::query()->when($activeYear, function ($q) use ($activeYear) {
            $q->whereHas('jadwalPelajaran', fn ($jq) => $jq->where('tahun_ajaran_id', $activeYear->id));
        });
        $averageGrade = round((float) ((clone $nilaiScope)->avg('nilai_akhir') ?? 0), 2);
        $gradeDistribution = [
            'A' => (clone $nilaiScope)->where('grade', 'A')->count(),
            'B' => (clone $nilaiScope)->where('grade', 'B')->count(),
            'C' => (clone $nilaiScope)->where('grade', 'C')->count(),
            'D' => (clone $nilaiScope)->where('grade', 'D')->count(),
            'E' => (clone $nilaiScope)->where('grade', 'E')->count(),
        ];

        // 4. Indikator Ketuntasan Input Nilai Rapor oleh Guru & Rekap Per Kelas
        $schedulesInPeriod = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'pengajar'])
            ->when($activeYear, fn ($q) => $q->where('tahun_ajaran_id', $activeYear->id))
            ->get();

        $activeSantriIdsByClass = Santri::where('status_santri', 'Aktif')
            ->get(['id', 'kelas_id'])
            ->groupBy('kelas_id')
            ->map(fn ($items) => $items->pluck('id')->all());

        $expectedTotalEntries = 0;
        $filledTotalEntries = 0;
        $incompleteSchedules = [];

        foreach ($schedulesInPeriod as $sch) {
            $santriIds = $activeSantriIdsByClass->get($sch->kelas_id, []);
            $expected = count($santriIds);
            if ($expected === 0) {
                continue;
            }

            $filled = Nilai::where('jadwal_pelajaran_id', $sch->id)
                ->whereIn('santri_id', $santriIds)
                ->count();

            $expectedTotalEntries += $expected;
            $filledTotalEntries += min($filled, $expected);

            if ($filled < $expected) {
                $incompleteSchedules[] = [
                    'mapel' => $sch->mataPelajaran->nama_mapel ?? '-',
                    'kelas' => $sch->kelas->nama_kelas ?? '-',
                    'pengajar' => $sch->pengajar->nama_lengkap ?? '-',
                    'filled' => $filled,
                    'expected' => $expected,
                    'percentage' => round(($filled / $expected) * 100, 1),
                ];
            }
        }

        $gradeCompletionPercentage = $expectedTotalEntries > 0
            ? round(($filledTotalEntries / $expectedTotalEntries) * 100, 1)
            : 0;

        // 5. Rekapitulasi Lengkap Per Kelas
        $classSummaries = $classes->map(function (Kelas $kelas) use ($activeYear, $activeSantriIdsByClass, $schedulesInPeriod) {
            $santriIds = $activeSantriIdsByClass->get($kelas->id, []);
            $santriCount = count($santriIds);

            $classSchedules = $schedulesInPeriod->where('kelas_id', $kelas->id);
            $scheduleIds = $classSchedules->pluck('id');

            $expectedClassGrades = $santriCount * $scheduleIds->count();
            $filledClassGrades = ($santriCount > 0 && $scheduleIds->isNotEmpty())
                ? Nilai::whereIn('jadwal_pelajaran_id', $scheduleIds)->whereIn('santri_id', $santriIds)->count()
                : 0;

            $completionPct = $expectedClassGrades > 0
                ? round((min($filledClassGrades, $expectedClassGrades) / $expectedClassGrades) * 100, 1)
                : 0;

            $avgClassGrade = ($santriCount > 0 && $scheduleIds->isNotEmpty())
                ? round((float) (Nilai::whereIn('jadwal_pelajaran_id', $scheduleIds)->whereIn('santri_id', $santriIds)->avg('nilai_akhir') ?? 0), 2)
                : 0;

            $absQuery = ($santriCount > 0 && $scheduleIds->isNotEmpty())
                ? Absensi::whereIn('jadwal_pelajaran_id', $scheduleIds)->whereIn('santri_id', $santriIds)
                : null;

            $totAbs = $absQuery ? (clone $absQuery)->count() : 0;
            $totHdr = $absQuery ? (clone $absQuery)->where('status_kehadiran', 'Hadir')->count() : 0;
            $attPct = $totAbs > 0 ? round(($totHdr / $totAbs) * 100, 1) : 0;

            return [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'tingkat_kelas' => $kelas->tingkat_kelas,
                'wali_kelas' => $kelas->waliKelas->nama_lengkap ?? '-',
                'jumlah_santri' => $santriCount,
                'jumlah_mapel' => $scheduleIds->count(),
                'attendance_pct' => $attPct,
                'avg_grade' => $avgClassGrade,
                'completion_pct' => $completionPct,
                'filled_grades' => min($filledClassGrades, $expectedClassGrades),
                'expected_grades' => $expectedClassGrades,
            ];
        });

        return [
            'activeYear' => $activeYear,
            'totalSantri' => $totalSantriAktif,
            'totalSantriAktif' => $totalSantriAktif,
            'totalSantriLulus' => $totalSantriLulus,
            'totalSantriMutasi' => $totalSantriMutasi,
            'totalPengajar' => $totalPengajar,
            'totalKelas' => $totalKelas,
            'ppdbTrends' => $ppdbRaw,
            'ppdbTotals' => $ppdbTotals,
            'attendancePercentage' => $attendancePercentage,
            'averageGrade' => $averageGrade,
            'gradeDistribution' => $gradeDistribution,
            'expectedTotalEntries' => $expectedTotalEntries,
            'filledTotalEntries' => $filledTotalEntries,
            'gradeCompletionPercentage' => $gradeCompletionPercentage,
            'incompleteSchedules' => $incompleteSchedules,
            'classSummaries' => $classSummaries,
        ];
    }
}
