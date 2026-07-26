<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->paginate(10);
        return view('admin.academic-years.index', compact('tahunAjarans'));
    }

    public function create()
    {
        return view('admin.academic-years.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->has('status_aktif') && $request->status_aktif) {
            TahunAjaran::query()->update(['status_aktif' => false]);
            $validated['status_aktif'] = true;
        } else {
            $validated['status_aktif'] = false;
        }

        TahunAjaran::create($validated);

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAjaran $academicYear)
    {
        return view('admin.academic-years.edit', ['tahunAjaran' => $academicYear]);
    }

    public function update(Request $request, TahunAjaran $academicYear)
    {
        $validated = $request->validate([
            'nama_tahun_ajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->has('status_aktif') && $request->status_aktif) {
            TahunAjaran::where('id', '!=', $academicYear->id)->update(['status_aktif' => false]);
            $validated['status_aktif'] = true;
        }

        $academicYear->update($validated);

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Ajaran berhasil diperbarui.');
    }

    public function toggleActive(TahunAjaran $academicYear)
    {
        TahunAjaran::query()->update(['status_aktif' => false]);
        $academicYear->update(['status_aktif' => true]);

        return redirect()->back()->with('success', "Tahun Ajaran {$academicYear->nama_tahun_ajaran} {$academicYear->semester} diaktifkan.");
    }

    public function destroy(TahunAjaran $academicYear)
    {
        if ($academicYear->status_aktif) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus tahun ajaran yang sedang aktif.');
        }

        $academicYear->delete();
        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun Ajaran berhasil dihapus.');
    }
}
