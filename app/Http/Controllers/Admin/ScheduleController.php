<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengajar;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalPelajaran::with(['tahunAjaran', 'kelas.tahunAjaran', 'mataPelajaran', 'pengajar']);

        $taAktif = TahunAjaran::getAktif();
        $selectedTahunAjaranId = $request->has('tahun_ajaran_id')
            ? $request->input('tahun_ajaran_id')
            : ($taAktif?->id ?? 'all');

        if ($selectedTahunAjaranId && $selectedTahunAjaranId !== 'all') {
            $query->where('tahun_ajaran_id', $selectedTahunAjaranId);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('pengajar_id')) {
            $query->where('pengajar_id', $request->pengajar_id);
        }

        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        $schedules = $query->orderByHari()
                           ->orderBy('waktu_mulai', 'asc')
                           ->paginate(15)
                           ->withQueryString();

        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->get();
        $kelasses = Kelas::with('tahunAjaran')->orderBy('nama_kelas')->get();
        $pengajars = Pengajar::where('status_pengajar', 'Aktif')->orderBy('nama_lengkap')->get();

        return view('admin.schedules.index', compact('schedules', 'tahunAjarans', 'kelasses', 'pengajars', 'selectedTahunAjaranId', 'taAktif'));
    }

    public function create()
    {
        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->get();
        $taAktif = TahunAjaran::getAktif();
        $kelasses = Kelas::orderBy('nama_kelas')->get();
        $subjects = MataPelajaran::orderBy('nama_mapel')->get();
        $pengajars = Pengajar::where('status_pengajar', 'Aktif')->orderBy('nama_lengkap')->get();

        return view('admin.schedules.create', compact('tahunAjarans', 'taAktif', 'kelasses', 'subjects', 'pengajars'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'kelas_id' => 'required|exists:kelasses,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'pengajar_id' => 'required|exists:pengajars,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'ruang' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        // 🔍 Conflict Checking
        $conflictError = $this->checkScheduleConflict(
            $validated['tahun_ajaran_id'],
            $validated['pengajar_id'],
            $validated['kelas_id'],
            $validated['hari'],
            $validated['waktu_mulai'],
            $validated['waktu_selesai'],
            $validated['ruang'] ?? null
        );

        if ($conflictError) {
            return back()->withInput()->withErrors($conflictError);
        }

        JadwalPelajaran::create($validated);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function edit(JadwalPelajaran $schedule)
    {
        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->get();
        $kelasses = Kelas::orderBy('nama_kelas')->get();
        $subjects = MataPelajaran::orderBy('nama_mapel')->get();
        $pengajars = Pengajar::where('status_pengajar', 'Aktif')->orderBy('nama_lengkap')->get();

        return view('admin.schedules.edit', compact('schedule', 'tahunAjarans', 'kelasses', 'subjects', 'pengajars'));
    }

    public function update(Request $request, JadwalPelajaran $schedule)
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'kelas_id' => 'required|exists:kelasses,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'pengajar_id' => 'required|exists:pengajars,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required|after:waktu_mulai',
            'ruang' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ]);

        // Standardize time format to H:i
        $validated['waktu_mulai'] = date('H:i', strtotime($validated['waktu_mulai']));
        $validated['waktu_selesai'] = date('H:i', strtotime($validated['waktu_selesai']));

        // 🔍 Conflict Checking (Excluding current schedule)
        $conflictError = $this->checkScheduleConflict(
            $validated['tahun_ajaran_id'],
            $validated['pengajar_id'],
            $validated['kelas_id'],
            $validated['hari'],
            $validated['waktu_mulai'],
            $validated['waktu_selesai'],
            $validated['ruang'] ?? null,
            $schedule->id
        );

        if ($conflictError) {
            return back()->withInput()->withErrors($conflictError);
        }

        $schedule->update($validated);

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function destroy(JadwalPelajaran $schedule)
    {
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    /**
     * Algoritma Deteksi Benturan Jadwal (Pengajar, Kelas, dan Ruang Kelas — FR-08)
     */
    private function checkScheduleConflict($tahunAjaranId, $pengajarId, $kelasId, $hari, $mulai, $selesai, $ruang = null, $ignoreId = null)
    {
        // 1. Cek Benturan Jadwal Ustaz / Pengajar
        $conflictPengajar = JadwalPelajaran::with(['pengajar', 'kelas'])
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('pengajar_id', $pengajarId)
            ->where('hari', $hari)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($mulai, $selesai) {
                $q->where('waktu_mulai', '<', $selesai)
                  ->where('waktu_selesai', '>', $mulai);
            })->first();

        if ($conflictPengajar) {
            $namaPengajar = $conflictPengajar->pengajar->nama_lengkap ?? 'Pengajar';
            $namaKelas = $conflictPengajar->kelas->nama_kelas ?? 'Kelas lain';
            return [
                'pengajar_id' => "BENTURAN PENGAJAR: {$namaPengajar} sudah memiliki jadwal di {$namaKelas} pada hari {$hari} jam {$conflictPengajar->waktu_mulai} - {$conflictPengajar->waktu_selesai}."
            ];
        }

        // 2. Cek Benturan Jadwal Kelas
        $conflictKelas = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('kelas_id', $kelasId)
            ->where('hari', $hari)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($mulai, $selesai) {
                $q->where('waktu_mulai', '<', $selesai)
                  ->where('waktu_selesai', '>', $mulai);
            })->first();

        if ($conflictKelas) {
            $namaKelas = $conflictKelas->kelas->nama_kelas ?? 'Kelas';
            $namaMapel = $conflictKelas->mataPelajaran->nama_mapel ?? 'Mapel lain';
            return [
                'kelas_id' => "BENTURAN KELAS: {$namaKelas} sudah memiliki mata pelajaran ({$namaMapel}) pada hari {$hari} jam {$conflictKelas->waktu_mulai} - {$conflictKelas->waktu_selesai}."
            ];
        }

        // 3. Cek Benturan Ruang Kelas (FR-08)
        $cleanRuang = trim((string) $ruang);
        if ($cleanRuang !== '') {
            $conflictRuang = JadwalPelajaran::with(['kelas', 'mataPelajaran'])
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where('hari', $hari)
                ->whereRaw('LOWER(TRIM(ruang)) = ?', [strtolower($cleanRuang)])
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where(function ($q) use ($mulai, $selesai) {
                    $q->where('waktu_mulai', '<', $selesai)
                      ->where('waktu_selesai', '>', $mulai);
                })->first();

            if ($conflictRuang) {
                $namaKelas = $conflictRuang->kelas->nama_kelas ?? 'Kelas lain';
                $namaMapel = $conflictRuang->mataPelajaran->nama_mapel ?? 'Mapel lain';
                return [
                    'ruang' => "BENTURAN RUANG: Ruang '{$cleanRuang}' sudah digunakan oleh {$namaKelas} ({$namaMapel}) pada hari {$hari} jam {$conflictRuang->waktu_mulai} - {$conflictRuang->waktu_selesai}."
                ];
            }
        }

        return null;
    }
}
