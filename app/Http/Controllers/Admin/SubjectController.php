<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalUnit;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = MataPelajaran::with('educationalUnit');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('kode_mapel', 'like', "%{$search}%")
                    ->orWhere('nama_mapel', 'like', "%{$search}%");
            });
        }

        $subjects = $query->orderBy('kode_mapel', 'asc')->paginate(10);
        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        $educationalUnits = EducationalUnit::where('is_active', true)->orderBy('nama_unit')->get();
        return view('admin.subjects.create', compact('educationalUnits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'educational_unit_id' => 'nullable|exists:educational_units,id',
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajarans,kode_mapel',
            'nama_mapel' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        MataPelajaran::create($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $subject)
    {
        $educationalUnits = EducationalUnit::where('is_active', true)->orderBy('nama_unit')->get();
        return view('admin.subjects.edit', compact('subject', 'educationalUnits'));
    }

    public function update(Request $request, MataPelajaran $subject)
    {
        $validated = $request->validate([
            'educational_unit_id' => 'nullable|exists:educational_units,id',
            'kode_mapel' => 'required|string|max:20|unique:mata_pelajarans,kode_mapel,' . $subject->id,
            'nama_mapel' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $subject)
    {
        $subject->delete();
        return redirect()->route('admin.subjects.index')->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
