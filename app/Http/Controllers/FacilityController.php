<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Http\Requests\StoreFacilityRequest;
use App\Http\Requests\UpdateFacilityRequest;
use App\Services\ImageCropService;
use Illuminate\Support\Facades\Storage;

class FacilityController extends Controller
{
    public function __construct(
        protected ImageCropService $imageCrop
    ) {}
    public function index()
    {
        $facilities = Facility::latest()->paginate(10);
        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('admin.facilities.create');
    }

    public function store(StoreFacilityRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('icon_or_image_path')) {
            $validated['icon_or_image_path'] = $this->imageCrop->storeCropped(
                $request->file('icon_or_image_path'),
                $request,
                'icon_or_image_path',
                'facilities'
            );
        }

        Facility::create($validated);

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(UpdateFacilityRequest $request, Facility $facility)
    {
        $validated = $request->validated();

        if ($request->hasFile('icon_or_image_path')) {
            if ($facility->icon_or_image_path && Storage::disk('public')->exists($facility->icon_or_image_path)) {
                Storage::disk('public')->delete($facility->icon_or_image_path);
            }
            $validated['icon_or_image_path'] = $this->imageCrop->storeCropped(
                $request->file('icon_or_image_path'),
                $request,
                'icon_or_image_path',
                'facilities'
            );
        }

        $facility->update($validated);

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $facility)
    {
        if ($facility->icon_or_image_path && Storage::disk('public')->exists($facility->icon_or_image_path)) {
            Storage::disk('public')->delete($facility->icon_or_image_path);
        }
        
        $facility->delete();

        return redirect()->route('admin.facilities.index')->with('success', 'Fasilitas berhasil dihapus.');
    }
}
