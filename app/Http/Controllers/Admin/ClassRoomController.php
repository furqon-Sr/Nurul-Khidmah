<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalUnit;
use App\Models\Kelas;
use App\Models\Pengajar;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassRoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::with(['waliKelas', 'tahunAjaran'])->withCount('santris');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('kode_kelas', 'like', "%{$search}%")
                    ->orWhere('nama_kelas', 'like', "%{$search}%");
            });
        }

        $classes = $query->orderBy('kode_kelas', 'asc')->paginate(10)->withQueryString();

        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $pengajars = Pengajar::where('status_pengajar', 'Aktif')->orderBy('nama_lengkap')->get();
        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->get();
        $taAktif = TahunAjaran::getAktif();
        $educationalUnits = EducationalUnit::where('is_active', true)->orderBy('nama_unit')->get();

        return view('admin.classes.create', compact('pengajars', 'tahunAjarans', 'taAktif', 'educationalUnits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'educational_unit_id' => 'nullable|exists:educational_units,id',
            'kode_kelas' => [
                'required',
                'string',
                'max:20',
                Rule::unique('kelasses', 'kode_kelas')->where('tahun_ajaran_id', $request->input('tahun_ajaran_id')),
            ],
            'nama_kelas' => 'required|string|max:50',
            'tingkat_kelas' => 'required|string|max:30',
            'wali_kelas_id' => 'nullable|exists:pengajars,id',
            'keterangan' => 'nullable|string',
        ], [
            'kode_kelas.unique' => 'Kode kelas sudah digunakan pada Tahun Ajaran yang dipilih.',
        ]);

        Kelas::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Data Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $class)
    {
        $pengajars = Pengajar::where('status_pengajar', 'Aktif')->orderBy('nama_lengkap')->get();
        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->get();
        $educationalUnits = EducationalUnit::where('is_active', true)->orderBy('nama_unit')->get();

        return view('admin.classes.edit', ['kelas' => $class, 'pengajars' => $pengajars, 'tahunAjarans' => $tahunAjarans, 'educationalUnits' => $educationalUnits]);
    }

    public function update(Request $request, Kelas $class)
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'educational_unit_id' => 'nullable|exists:educational_units,id',
            'kode_kelas' => [
                'required',
                'string',
                'max:20',
                Rule::unique('kelasses', 'kode_kelas')
                    ->where('tahun_ajaran_id', $request->input('tahun_ajaran_id'))
                    ->ignore($class->id),
            ],
            'nama_kelas' => 'required|string|max:50',
            'tingkat_kelas' => 'required|string|max:30',
            'wali_kelas_id' => 'nullable|exists:pengajars,id',
            'keterangan' => 'nullable|string',
        ], [
            'kode_kelas.unique' => 'Kode kelas sudah digunakan pada Tahun Ajaran yang dipilih.',
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
