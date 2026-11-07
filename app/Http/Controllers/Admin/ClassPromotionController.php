<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\RaporSemester;
use App\Models\RiwayatKelasSantri;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassPromotionController extends Controller
{
    /** Ambang rata-rata semester (KKM) yang menandai santri perlu ditinjau. */
    private const PASSING_AVERAGE = 65;

    /** Keputusan yang mensyaratkan kelengkapan nilai rapor. */
    private const GATED_ACTIONS = ['Naik Kelas', 'Lulus'];

    /**
     * Halaman Kenaikan & Kelulusan Santri (alur bertahap).
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

        // Filter tahun ajaran sumber: mengikuti kelas terpilih; bila belum ada, tahun ajaran aktif.
        $requestedAsal = $request->filled('asal_kelas_id') ? $allClasses->firstWhere('id', (int) $request->input('asal_kelas_id')) : null;
        $yearId = $requestedAsal?->tahun_ajaran_id
            ?? ($request->filled('tahun_ajaran_id') ? (int) $request->input('tahun_ajaran_id') : null)
            ?? $activeYear?->id;

        $sourceClasses = $allClasses->where('tahun_ajaran_id', $yearId)->values();
        if ($sourceClasses->isEmpty()) {
            $sourceClasses = $allClasses;
            $yearId = null;
        }

        $asalKelas = $requestedAsal && $sourceClasses->contains('id', $requestedAsal->id)
            ? $requestedAsal
            : ($sourceClasses->firstWhere('active_santris_count', '>', 0) ?? $sourceClasses->first());

        $santriRows = [];
        if ($asalKelas) {
            $santris = Santri::with('user')
                ->where('kelas_id', $asalKelas->id)
                ->where('status_santri', 'Aktif')
                ->orderBy('nama_lengkap')
                ->get();

            $readiness = $this->assessReadiness($asalKelas, $santris);

            $santriRows = $santris->map(fn (Santri $st) => [
                'id' => $st->id,
                'name' => $st->nama_lengkap,
                'nis' => $st->nis,
                'count' => $readiness[$st->id]['count'],
                'expected' => $readiness[$st->id]['expected'],
                'avg' => $readiness[$st->id]['avg'],
                'grade' => $readiness[$st->id]['avg'] !== null ? Nilai::calculateGrade($readiness[$st->id]['avg']) : null,
                'status' => $readiness[$st->id]['status'],
                'issues' => $readiness[$st->id]['issues'],
                'sikap' => $st->nilai_sikap ?: null,
                'waliEmail' => $st->user?->email,
            ])->values()->all();
        }

        $recentLogs = RiwayatKelasSantri::with(['santri', 'kelasLama', 'kelasBaru', 'tahunAjaran', 'creator'])
            ->latest()
            ->take(15)
            ->get();

        $canOverride = (bool) $request->user()?->isSuperAdmin();

        return view('admin.promotions.index', compact(
            'activeYear',
            'academicYears',
            'yearId',
            'allClasses',
            'sourceClasses',
            'asalKelas',
            'santriRows',
            'recentLogs',
            'canOverride'
        ));
    }

    /**
     * Penilaian kesiapan akademik setiap santri pada kelas asal (sumber kebenaran tunggal
     * untuk tampilan maupun validasi saat pemrosesan).
     *
     * - incomplete : belum ada satu pun nilai pada kelas & tahun ajaran sumber (memblokir kenaikan/kelulusan)
     * - review     : nilai belum lengkap untuk seluruh mapel, atau rata-rata di bawah ambang
     * - siap       : seluruh mapel terisi dan rata-rata memenuhi ambang
     *
     * @param  Collection<int, Santri>  $santris
     * @return array<int, array{count:int, expected:int, avg:?float, status:string, issues:list<string>}>
     */
    private function assessReadiness(Kelas $asalKelas, Collection $santris): array
    {
        $sourceYearId = $asalKelas->tahun_ajaran_id ?? TahunAjaran::getAktif()?->id;

        $expected = JadwalPelajaran::where('kelas_id', $asalKelas->id)
            ->when($sourceYearId, fn ($q) => $q->where('tahun_ajaran_id', $sourceYearId))
            ->count();

        $nilaiBySantri = Nilai::whereIn('santri_id', $santris->pluck('id'))
            ->whereHas('jadwalPelajaran', function ($jq) use ($asalKelas, $sourceYearId) {
                $jq->where('kelas_id', $asalKelas->id);
                if ($sourceYearId) {
                    $jq->where('tahun_ajaran_id', $sourceYearId);
                }
            })
            ->get()
            ->groupBy('santri_id');

        $result = [];
        foreach ($santris as $santri) {
            $list = $nilaiBySantri->get($santri->id, collect());
            $count = $list->count();
            $avg = $count > 0 ? round((float) $list->avg('nilai_akhir'), 2) : null;
            $issues = [];

            if ($count === 0) {
                $status = 'incomplete';
                $issues[] = 'Belum ada nilai pada kelas ini.';
            } else {
                if ($expected > $count) {
                    $issues[] = ($expected - $count).' mata pelajaran belum dinilai.';
                }
                if ($avg !== null && $avg < self::PASSING_AVERAGE) {
                    $issues[] = 'Rata-rata semester di bawah '.self::PASSING_AVERAGE.'.';
                }
                $status = $issues === [] ? 'siap' : 'review';
            }

            $result[$santri->id] = compact('count', 'expected', 'avg', 'status', 'issues');
        }

        return $result;
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
            'override' => ['nullable', 'boolean'],
            'override_reason' => ['nullable', 'string', 'min:10', 'max:255'],
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

        // Validasi kesiapan akademik di server (tidak hanya di tampilan).
        $overrideNote = null;
        if (in_array($jenisAksi, self::GATED_ACTIONS, true)) {
            $readiness = $this->assessReadiness($asalKelas, $selectedSantris);
            $blocked = $selectedSantris->filter(fn (Santri $st) => $readiness[$st->id]['status'] === 'incomplete');

            if ($blocked->isNotEmpty()) {
                $names = $blocked->pluck('nama_lengkap')->take(5)->implode(', ').($blocked->count() > 5 ? ', dan '.($blocked->count() - 5).' lainnya' : '');

                if (! $request->boolean('override')) {
                    return back()->withErrors([
                        'santri_ids' => "Proses dibatalkan: {$blocked->count()} santri belum memiliki nilai rapor ({$names}). Lengkapi nilai terlebih dahulu atau ajukan pengecualian resmi.",
                    ])->withInput();
                }

                if (! $request->user()->isSuperAdmin()) {
                    return back()->withErrors([
                        'override' => 'Pengecualian hanya dapat diberikan oleh Super Admin.',
                    ])->withInput();
                }

                if (blank($validated['override_reason'] ?? null)) {
                    return back()->withErrors([
                        'override_reason' => 'Alasan pengecualian wajib diisi (minimal 10 karakter).',
                    ])->withInput();
                }

                $overrideNote = "Pengecualian ({$blocked->count()} santri tanpa nilai: {$names}) oleh ".$request->user()->name.": {$validated['override_reason']}";
            }
        }

        if ($overrideNote) {
            // Alasan pengecualian tercatat pada riwayat tiap santri (kolom catatan maks. 255 karakter).
            $validated['catatan'] = Str::limit(trim(($validated['catatan'] ?? '').' [Pengecualian: '.$validated['override_reason'].']'), 250, '');
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

        if ($overrideNote) {
            $desc .= ' | '.$overrideNote;
        }

        AdminActivityLog::log('update', Str::limit($desc, 250, ''), 'Kelas', $asalKelas->id);

        return redirect()
            ->route('admin.promotions.index', ['asal_kelas_id' => $tujuanKelas?->id ?? $asalKelas->id])
            ->with('success', "Berhasil! {$desc}. Seluruh riwayat nilai & akun Portal Wali Santri tetap terjaga.");
    }
}
