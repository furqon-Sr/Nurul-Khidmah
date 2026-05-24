<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationProgram;
use App\Services\ImageCropService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function __construct(
        protected ImageCropService $imageCrop
    ) {}
    public function index()
    {
        // Graceful fallback if table doesn't exist yet (migration not run)
        if (! \Illuminate\Support\Facades\Schema::hasTable('education_programs')) {
            return view('admin.programs.index', ['programs' => collect()])
                ->with('warning', 'Tabel education_programs belum ada. Jalankan: php artisan migrate di terminal.');
        }

        $programs = EducationProgram::orderBy('order')->orderBy('name')->get();
        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        return view('admin.programs.create');
    }

    public function store(Request $request)
    {
        $request->validate(array_merge([
            'name'                 => 'required|string|max:100',
            'description'          => 'nullable|string',
            'image'                => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
            'is_active'            => 'nullable|boolean',
            'is_registration_open' => 'nullable|boolean',
            'is_coming_soon'       => 'nullable|boolean',
            'order'                => 'nullable|integer|min:0',
        ], $this->imageCrop->cropRules('image')));

        $slug = Str::slug($request->name);
        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        while (EducationProgram::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data = [
            'name'                 => $request->name,
            'slug'                 => $slug,
            'description'          => $request->description,
            'is_active'            => $request->has('is_active') ? true : false,
            'is_registration_open' => $request->has('is_registration_open') ? true : false,
            'is_coming_soon'       => $request->has('is_coming_soon') ? true : false,
            'order'                => $request->order ?? 0,
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->imageCrop->storeCropped(
                $request->file('image'),
                $request,
                'image',
                'programs'
            );
        }

        EducationProgram::create($data);
        Cache::forget('site_global_data');

        return redirect()->route('admin.programs.index')
            ->with('success', "Program '{$request->name}' berhasil ditambahkan.");
    }

    public function edit(EducationProgram $program)
    {
        return view('admin.programs.edit', compact('program'));
    }

    public function update(Request $request, EducationProgram $program)
    {
        $request->validate(array_merge([
            'name'                 => 'required|string|max:100',
            'description'          => 'nullable|string',
            'image'                => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
            'is_active'            => 'nullable|boolean',
            'is_registration_open' => 'nullable|boolean',
            'is_coming_soon'       => 'nullable|boolean',
            'order'                => 'nullable|integer|min:0',
        ], $this->imageCrop->cropRules('image')));

        $data = [
            'name'                 => $request->name,
            'description'          => $request->description,
            'is_active'            => $request->has('is_active') ? true : false,
            'is_registration_open' => $request->has('is_registration_open') ? true : false,
            'is_coming_soon'       => $request->has('is_coming_soon') ? true : false,
            'order'                => $request->order ?? 0,
        ];

        if ($request->hasFile('image')) {
            if ($program->image_path) {
                Storage::disk('public')->delete($program->image_path);
            }
            $data['image_path'] = $this->imageCrop->storeCropped(
                $request->file('image'),
                $request,
                'image',
                'programs'
            );
        }

        $program->update($data);
        Cache::forget('site_global_data');

        return redirect()->route('admin.programs.index')
            ->with('success', "Program '{$program->name}' berhasil diperbarui.");
    }

    public function destroy(EducationProgram $program)
    {
        if ($program->image_path) {
            Storage::disk('public')->delete($program->image_path);
        }
        $name = $program->name;
        $program->delete();
        Cache::forget('site_global_data');

        return redirect()->route('admin.programs.index')
            ->with('success', "Program '{$name}' berhasil dihapus.");
    }

    /**
     * Toggle is_active or is_registration_open via AJAX/form POST.
     */
    public function toggle(Request $request, EducationProgram $program)
    {
        $field = $request->input('field');

        if (! in_array($field, ['is_active', 'is_registration_open', 'is_coming_soon'])) {
            return response()->json(['error' => 'Invalid field'], 422);
        }

        $program->update([$field => ! $program->$field]);
        Cache::forget('site_global_data');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'field'   => $field,
                'value'   => $program->$field,
            ]);
        }

        return back()->with('success', 'Status program berhasil diperbarui.');
    }
}
