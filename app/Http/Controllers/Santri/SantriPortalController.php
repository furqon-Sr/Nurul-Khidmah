<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\PageContent;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Services\AcademicRaporService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SantriPortalController extends Controller
{
    public function __construct(
        protected AcademicRaporService $raporService
    ) {}

    public function dashboard(Request $request)
    {
        [$santri, $allSantrisForPreview, $previewClasses, $totalMatchingSantris] = $this->resolveSantriForPortal($request);
        $activeYear = TahunAjaran::getAktif();

        if (! $santri) {
            return view('santri.dashboard', [
                'santri' => null,
                'tahunAjaran' => $activeYear,
                'availableYears' => collect(),
                'kelasPeriode' => null,
                'allSantrisForPreview' => $allSantrisForPreview,
                'previewClasses' => $previewClasses,
                'totalMatchingSantris' => $totalMatchingSantris,
                'schedules' => collect(),
                'totalHadir' => 0,
                'totalIzin' => 0,
                'totalSakit' => 0,
                'totalAlpa' => 0,
                'gpa' => 0,
            ]);
        }

        $santri->load(['kelas', 'kelas.waliKelas', 'kelas.tahunAjaran']);
        [$tahunAjaran, $availableYears] = $this->resolveSelectedYear($request, $santri);
        $raporData = $this->raporService->buildSantriRaporData($santri, $tahunAjaran);
        $kelasPeriode = $raporData['kelasPeriode'];

        $schedules = collect();
        if ($kelasPeriode) {
            $schedules = JadwalPelajaran::with(['mataPelajaran', 'pengajar', 'tahunAjaran'])
                ->where('kelas_id', $kelasPeriode->id)
                ->when($tahunAjaran, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
                ->orderByHari()
                ->orderBy('waktu_mulai')
                ->get();
        }

        return view('santri.dashboard', [
            'santri' => $santri,
            'tahunAjaran' => $tahunAjaran,
            'availableYears' => $availableYears,
            'kelasPeriode' => $kelasPeriode,
            'allSantrisForPreview' => $allSantrisForPreview,
            'previewClasses' => $previewClasses,
            'totalMatchingSantris' => $totalMatchingSantris,
            'schedules' => $schedules,
            'totalHadir' => $raporData['totalHadir'],
            'totalIzin' => $raporData['totalIzin'],
            'totalSakit' => $raporData['totalSakit'],
            'totalAlpa' => $raporData['totalAlpa'],
            'gpa' => $raporData['rataRata'],
            'catatanWaliKelas' => $raporData['catatanWaliKelas'],
            'nilaiSikap' => $raporData['nilaiSikap'],
            'raporSemester' => $raporData['raporSemester'],
        ]);
    }

    public function attendance(Request $request)
    {
        [$santri, $allSantrisForPreview, $previewClasses, $totalMatchingSantris] = $this->resolveSantriForPortal($request);

        if (! $santri) {
            return redirect()->route('wali.dashboard')->with('error', 'Data Santri tidak ditemukan.');
        }

        $santri->load(['kelas', 'kelas.tahunAjaran']);
        [$tahunAjaran, $availableYears] = $this->resolveSelectedYear($request, $santri);
        $kelasPeriode = $this->raporService->resolveKelasForSantriInYear($santri, $tahunAjaran);

        $absensiRecords = Absensi::with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.pengajar'])
            ->where('santri_id', $santri->id)
            ->when($tahunAjaran, function ($q) use ($tahunAjaran) {
                $q->whereHas('jadwalPelajaran', fn ($jq) => $jq->where('tahun_ajaran_id', $tahunAjaran->id));
            })
            ->orderBy('tanggal', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('santri.attendance', compact(
            'santri',
            'tahunAjaran',
            'availableYears',
            'kelasPeriode',
            'allSantrisForPreview',
            'previewClasses',
            'totalMatchingSantris',
            'absensiRecords'
        ));
    }

    public function grades(Request $request)
    {
        [$santri, $allSantrisForPreview, $previewClasses, $totalMatchingSantris] = $this->resolveSantriForPortal($request);

        if (! $santri) {
            return redirect()->route('wali.dashboard')->with('error', 'Data Santri tidak ditemukan.');
        }

        $santri->load(['kelas', 'kelas.waliKelas', 'kelas.tahunAjaran']);
        [$tahunAjaran, $availableYears] = $this->resolveSelectedYear($request, $santri);
        $raporData = $this->raporService->buildSantriRaporData($santri, $tahunAjaran);

        return view('santri.grades', [
            'santri' => $santri,
            'tahunAjaran' => $tahunAjaran,
            'availableYears' => $availableYears,
            'kelasPeriode' => $raporData['kelasPeriode'],
            'allSantrisForPreview' => $allSantrisForPreview,
            'previewClasses' => $previewClasses,
            'totalMatchingSantris' => $totalMatchingSantris,
            'subjectRows' => $raporData['subjectRows'],
            'nilais' => $raporData['nilais'],
            'jumlahNilai' => $raporData['jumlahNilai'],
            'gpa' => $raporData['rataRata'],
            'catatanWaliKelas' => $raporData['catatanWaliKelas'],
            'nilaiSikap' => $raporData['nilaiSikap'],
            'raporSemester' => $raporData['raporSemester'],
        ]);
    }

    public function downloadRaporPdf(Request $request)
    {
        if ($request->has('download') || $request->input('format') === 'pdf') {
            return $this->downloadTruePdf($request);
        }

        [$santri] = $this->resolveSantriForPortal($request);

        if (! $santri) {
            return redirect()->back()->with('error', 'Data Santri tidak ditemukan.');
        }

        $santri->load(['kelas', 'kelas.waliKelas', 'kelas.tahunAjaran']);
        [$tahunAjaran] = $this->resolveSelectedYear($request, $santri);
        $raporData = $this->raporService->buildSantriRaporData($santri, $tahunAjaran);
        $siteInfo = PageContent::all()->keyBy('section_key');

        return view('santri.rapor-pdf', [
            'santri' => $santri,
            'tahunAjaran' => $tahunAjaran,
            'kelasPeriode' => $raporData['kelasPeriode'],
            'subjectRows' => $raporData['subjectRows'],
            'nilais' => $raporData['nilais'],
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

    public function downloadTruePdf(Request $request)
    {
        [$santri] = $this->resolveSantriForPortal($request);

        if (! $santri) {
            return redirect()->back()->with('error', 'Data Santri tidak ditemukan.');
        }

        $santri->load(['kelas', 'kelas.waliKelas', 'kelas.tahunAjaran']);
        [$tahunAjaran] = $this->resolveSelectedYear($request, $santri);
        $raporData = $this->raporService->buildSantriRaporData($santri, $tahunAjaran);
        $siteInfo = PageContent::all()->keyBy('section_key');

        $viewData = [
            'santri' => $santri,
            'tahunAjaran' => $tahunAjaran,
            'kelasPeriode' => $raporData['kelasPeriode'],
            'subjectRows' => $raporData['subjectRows'],
            'nilais' => $raporData['nilais'],
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

        $fileName = 'Rapor_'.Str::slug($santri->nama_lengkap).'_'.($santri->nis).'.pdf';

        return $pdf->download($fileName);
    }

    private function resolveSelectedYear(Request $request, Santri $santri): array
    {
        $availableYears = $this->raporService->resolveAvailableYearsForSantri($santri);
        $requestedYearId = $request->input('tahun_ajaran_id');

        if ($requestedYearId) {
            $selected = $availableYears->firstWhere('id', (int) $requestedYearId)
                ?? TahunAjaran::find((int) $requestedYearId);
            if ($selected) {
                return [$selected, $availableYears];
            }
        }

        $defaultYear = TahunAjaran::getAktif() ?? $santri->kelas?->tahunAjaran ?? $availableYears->first();

        return [$defaultYear, $availableYears];
    }

    /**
     * Resolusi data santri:
     * - Wali Santri (role=santri): otomatis terikat ke santri-santri ananda ($user->santris).
     *   Jika memiliki lebih dari 1 anak terdaftar, wali dapat memilih ananda aktif via session.
     * - Super Admin / Pengurus Akademik / Kepala Sekolah: mode pantau dilengkapi Filter Tingkat (7/8/9), Filter Kelas, & Search Cepat (Nama/NIS).
     */
    private function resolveSantriForPortal(Request $request): array
    {
        $user = Auth::user();

        if ($user->isPengurusAkademik() || $user->isKepalaSekolah() || $user->isPimpinan()) {
            $previewClasses = Kelas::with('tahunAjaran')
                ->orderBy('tingkat_kelas')
                ->orderBy('nama_kelas')
                ->get();

            $query = Santri::with('kelas')->orderBy('nama_lengkap');

            if ($request->filled('tingkat')) {
                $tingkat = trim($request->input('tingkat'));
                $query->whereHas('kelas', function ($kq) use ($tingkat) {
                    $kq->where('tingkat_kelas', 'like', "%{$tingkat}%")
                        ->orWhere('nama_kelas', 'like', "%{$tingkat}%");
                });
            }

            if ($request->filled('filter_kelas_id')) {
                $query->where('kelas_id', (int) $request->input('filter_kelas_id'));
            }

            if ($request->filled('search')) {
                $search = trim($request->input('search'));
                $query->where(function ($sq) use ($search) {
                    $sq->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            }

            $totalMatchingSantris = (clone $query)->count();
            $filteredSantris = $query->limit(60)->get();

            $requestedId = $request->input('santri_id');
            $santri = null;

            if ($request->filled('search') || $request->filled('filter_kelas_id') || $request->filled('tingkat')) {
                // Saat ada filter atau pencarian aktif: utamakan santri hasil pencarian
                if ($requestedId) {
                    $santri = $filteredSantris->firstWhere('id', (int) $requestedId);
                }

                // Jika santri_id lama tidak ada dalam hasil pencarian, ambil santri pertama yang cocok
                if (! $santri) {
                    $santri = $filteredSantris->firstWhere('status_santri', 'Aktif') ?? $filteredSantris->first();
                }
            } else {
                if ($requestedId) {
                    $santri = $filteredSantris->firstWhere('id', (int) $requestedId)
                        ?? Santri::with('kelas')->find((int) $requestedId);

                    if ($santri && ! $filteredSantris->contains('id', $santri->id)) {
                        $filteredSantris->prepend($santri);
                    }
                }

                if (! $santri) {
                    $santri = $filteredSantris->firstWhere('status_santri', 'Aktif') ?? $filteredSantris->first();
                }
            }

            return [$santri, $filteredSantris, $previewClasses, $totalMatchingSantris];
        }

        // Resolusi untuk Wali Santri (role = santri) dengan dukungan Multi-Anak
        $mySantris = $user->santris()->with(['kelas.waliKelas', 'kelas.tahunAjaran'])->get();

        $requestedId = $request->input('switch_santri_id', $request->input('santri_id', session('active_wali_santri_id')));
        $activeSantri = null;

        if ($requestedId) {
            $activeSantri = $mySantris->firstWhere('id', (int) $requestedId);
            if ($activeSantri) {
                session(['active_wali_santri_id' => $activeSantri->id]);
            }
        }

        if (! $activeSantri) {
            $activeSantri = $mySantris->first();
            if ($activeSantri) {
                session(['active_wali_santri_id' => $activeSantri->id]);
            }
        }

        return [$activeSantri, $mySantris, collect(), $mySantris->count()];
    }
}
