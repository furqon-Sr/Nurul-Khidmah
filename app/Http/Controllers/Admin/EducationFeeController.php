<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationFee;
use Illuminate\Http\Request;

class EducationFeeController extends Controller
{
    public function index()
    {
        $fees = EducationFee::orderBy('category')->orderBy('name')->get();
        return view('admin.fees.index', compact('fees'));
    }

    public function create()
    {
        $programs = \App\Models\EducationProgram::active()->orderBy('name')->get();
        return view('admin.fees.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        EducationFee::create($validated);

        return redirect()->route('admin.fees.index')->with('success', 'Biaya pendidikan berhasil ditambahkan.');
    }

    public function edit(EducationFee $fee)
    {
        $programs = \App\Models\EducationProgram::active()->orderBy('name')->get();
        return view('admin.fees.edit', compact('fee', 'programs'));
    }

    public function update(Request $request, EducationFee $fee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $fee->update($validated);

        return redirect()->route('admin.fees.index')->with('success', 'Biaya pendidikan berhasil diperbarui.');
    }

    public function destroy(EducationFee $fee)
    {
        $fee->delete();
        return redirect()->route('admin.fees.index')->with('success', 'Biaya pendidikan berhasil dihapus.');
    }
}
