<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pengajar;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class ClassRoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::with(['waliKelas', 'tahunAjaran'])->withCount('santris');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('kode_kelas', 'like', "%{$search}%")
                  ->orWhere('nama_kelas', 'like', "%{$search}%");
        }

        $classes = $query->orderBy('kode_kelas', 'asc')->paginate(10);
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $pengajars = Pengajar::where('status_pengajar', 'Aktif')->orderBy('nama_lengkap')->get();
        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->get();
        $taAktif = TahunAjaran::getAktif();

        return view('admin.classes.create', compact('pengajars', 'tahunAjarans', 'taAktif'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kelas' => 'required|string|max:20|unique:kelasses,kode_kelas',
            'nama_kelas' => 'required|string|max:50',
            'tingkat_kelas' => 'required|string|max:30',
            'wali_kelas_id' => 'nullable|exists:pengajars,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'keterangan' => 'nullable|string',
        ]);

        Kelas::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Data Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $class)
    {
        $pengajars = Pengajar::where('status_pengajar', 'Aktif')->orderBy('nama_lengkap')->get();
        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->get();

        return view('admin.classes.edit', ['kelas' => $class, 'pengajars' => $pengajars, 'tahunAjarans' => $tahunAjarans]);
    }

    public function update(Request $request, Kelas $class)
    {
        $validated = $request->validate([
            'kode_kelas' => 'required|string|max:20|unique:kelasses,kode_kelas,' . $class->id,
            'nama_kelas' => 'required|string|max:50',
            'tingkat_kelas' => 'required|string|max:30',
            'wali_kelas_id' => 'nullable|exists:pengajars,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'keterangan' => 'nullable|string',
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Data Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $class)
    {
        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Data Kelas berhasil dihapus.');
    }
}
