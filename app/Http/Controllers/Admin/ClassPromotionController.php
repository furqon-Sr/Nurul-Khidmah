<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\RaporSemester;
use App\Models\RiwayatKelasSantri;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassPromotionController extends Controller
{
    /**
     * Halaman Kenaikan Kelas & Kelulusan Kolektif Santri Lintas Tahun Ajaran.
     */
    public function index(Request $request): View
    {
        $activeYear = TahunAjaran::getAktif();
        $academicYears = TahunAjaran::orderBy('id', 'desc')->get();
        $allClasses = Kelas::with(['tahunAjaran', 'waliKelas'])
            ->withCount(['santris as active_santris_count' => fn ($q) => $q->where('status_santri', 'Aktif')])
            ->orderBy('tahun_ajaran_id', 'desc')
            ->orderBy('tingkat_kelas')
            ->orderBy('nama_kelas')
            ->get();

        $defaultAsalId = $allClasses->firstWhere('active_santris_count', '>', 0)?->id ?? $allClasses->first()?->id;
        $selectedAsalId = $request->input('asal_kelas_id', $defaultAsalId);
        $asalKelas = $selectedAsalId ? $allClasses->firstWhere('id', (int) $selectedAsalId) : null;

        $santris = collect();
        $gpaMap = [];

        if ($asalKelas) {
            $query = Santri::with('user')
                ->where('kelas_id', $asalKelas->id)
                ->where('status_santri', 'Aktif')
                ->orderBy('nama_lengkap');

            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('nama_lengkap', 'like', "%{$s}%")
                        ->orWhere('nis', 'like', "%{$s}%");
                });
            }

            $santris = $query->get();

            $sourceYearId = $asalKelas->tahun_ajaran_id ?? $activeYear?->id;
            foreach ($santris as $st) {
                $nilaiList = Nilai::where('santri_id', $st->id)
                    ->whereHas('jadwalPelajaran', function ($jq) use ($asalKelas, $sourceYearId) {
                        $jq->where('kelas_id', $asalKelas->id);
                        if ($sourceYearId) {
                            $jq->where('tahun_ajaran_id', $sourceYearId);
                        }
                    })
                    ->get();

                $gpaMap[$st->id] = [
                    'count' => $nilaiList->count(),
                    'avg' => $nilaiList->isNotEmpty() ? round($nilaiList->avg('nilai_akhir'), 2) : null,
                ];
            }
        }

        $recentLogs = RiwayatKelasSantri::with(['santri', 'kelasLama', 'kelasBaru', 'tahunAjaran', 'creator'])
            ->latest()
            ->take(15)
            ->get();

        return view('admin.promotions.index', compact(
            'activeYear',
            'academicYears',
            'allClasses',
            'asalKelas',
            'santris',
            'gpaMap',
            'recentLogs'
        ));
    }

    /**
     * Eksekusi kolektif Kenaikan Kelas / Tinggal Kelas / Pindah Kelas / Kelulusan Santri.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'asal_kelas_id' => ['required', 'exists:kelasses,id'],
            'santri_ids' => ['required', 'array', 'min:1'],
            'santri_ids.*' => ['exists:santris,id'],
            'jenis_aksi' => ['required', 'in:Naik Kelas,Tinggal Kelas,Pindah Kelas,Lulus'],
            'tujuan_kelas_id' => ['required_unless:jenis_aksi,Lulus', 'nullable', 'exists:kelasses,id'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'santri_ids.required' => 'Pilih minimal satu santri yang akan diproses.',
            'tujuan_kelas_id.required_unless' => 'Silakan pilih Kelas Tujuan untuk aksi kenaikan / penempatan kelas.',
        ]);

        $asalKelas = Kelas::with('tahunAjaran')->findOrFail($validated['asal_kelas_id']);
        $jenisAksi = $validated['jenis_aksi'];
        $tujuanKelas = null;

        if ($jenisAksi !== 'Lulus') {
            if ((int) $validated['tujuan_kelas_id'] === (int) $asalKelas->id) {
                return back()->withErrors(['tujuan_kelas_id' => 'Kelas tujuan tidak boleh sama dengan kelas asal.'])->withInput();
            }
            $tujuanKelas = Kelas::with('tahunAjaran')->findOrFail($validated['tujuan_kelas_id']);
        }

        $selectedSantris = Santri::where('kelas_id', $asalKelas->id)
            ->whereIn('id', $validated['santri_ids'])
            ->get();

        if ($selectedSantris->isEmpty()) {
            return back()->withErrors(['santri_ids' => 'Tidak ada santri valid dari kelas asal yang dipilih.']);
        }

        $activeYear = TahunAjaran::getAktif();

        DB::transaction(function () use ($selectedSantris, $asalKelas, $tujuanKelas, $jenisAksi, $validated, $activeYear) {
            foreach ($selectedSantris as $santri) {
                // Pastikan jejak kelas asal tercatat agar arsip rapor lintas tahun ajaran selalu akurat
                if ($asalKelas->tahun_ajaran_id) {
                    $hasOriginHistory = RiwayatKelasSantri::where('santri_id', $santri->id)
                        ->where('kelas_baru_id', $asalKelas->id)
                        ->exists();

                    if (! $hasOriginHistory) {
                        RiwayatKelasSantri::create([
                            'santri_id' => $santri->id,
                            'kelas_lama_id' => null,
                            'kelas_baru_id' => $asalKelas->id,
                            'tahun_ajaran_id' => $asalKelas->tahun_ajaran_id,
                            'jenis_aksi' => 'Arsip Kelas',
                            'catatan' => 'Rekam otomatis kelas asal sebelum promosi',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }

                $taAsalId = $asalKelas->tahun_ajaran_id ?? $activeYear?->id;
                if ($taAsalId) {
                    RaporSemester::updateOrCreate(
                        ['santri_id' => $santri->id, 'tahun_ajaran_id' => $taAsalId],
                        [
                            'kelas_id' => $asalKelas->id,
                            'status_kenaikan' => $jenisAksi,
                            'status_publikasi' => 'published',
                        ]
                    );
                }

                if ($jenisAksi === 'Lulus') {
                    $santri->update([
                        'status_santri' => 'Lulus',
                    ]);

                    RiwayatKelasSantri::create([
                        'santri_id' => $santri->id,
                        'kelas_lama_id' => $asalKelas->id,
                        'kelas_baru_id' => null,
                        'tahun_ajaran_id' => $asalKelas->tahun_ajaran_id ?? $activeYear?->id,
                        'jenis_aksi' => 'Lulus',
                        'catatan' => $validated['catatan'] ?: "Dinyatakan Lulus dari {$asalKelas->nama_kelas}",
                        'created_by' => auth()->id(),
                    ]);
                } else {
                    $santri->update([
                        'kelas_id' => $tujuanKelas->id,
                        'status_santri' => 'Aktif',
                    ]);

                    RiwayatKelasSantri::create([
                        'santri_id' => $santri->id,
                        'kelas_lama_id' => $asalKelas->id,
                        'kelas_baru_id' => $tujuanKelas->id,
                        'tahun_ajaran_id' => $tujuanKelas->tahun_ajaran_id ?? $activeYear?->id,
                        'jenis_aksi' => $jenisAksi,
                        'catatan' => $validated['catatan'] ?: "{$jenisAksi} dari {$asalKelas->nama_kelas} ke {$tujuanKelas->nama_kelas}",
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        });

        $count = $selectedSantris->count();
        $desc = $jenisAksi === 'Lulus'
            ? "Meluluskan kolektif {$count} santri dari {$asalKelas->nama_kelas} (akun Wali & NIS tetap tersimpan)"
            : "Memproses {$jenisAksi} untuk {$count} santri dari {$asalKelas->nama_kelas} ke {$tujuanKelas->nama_kelas}";

        AdminActivityLog::log('update', $desc, 'Kelas', $asalKelas->id);

        return redirect()
            ->route('admin.promotions.index', ['asal_kelas_id' => $tujuanKelas?->id ?? $asalKelas->id])
            ->with('success', "Berhasil! {$desc}. Seluruh riwayat nilai & akun Portal Wali Santri tetap terjaga.");
    }
}
