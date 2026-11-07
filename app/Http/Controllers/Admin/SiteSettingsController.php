<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hero;
use App\Models\PageContent;
use App\Services\ImageCropService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function __construct(
        protected ImageCropService $imageCrop
    ) {}
    public function index()
    {
        $heroes = Hero::orderBy('position')->get();
        
        // Pad to ensure we always have 3 heroes in the view for the form
        while ($heroes->count() < 3) {
            $heroes->push(new Hero(['position' => $heroes->count() + 1]));
        }

        $contents = PageContent::all()->keyBy('section_key');

        return view('admin.settings.index', compact('heroes', 'contents'));
    }

    public function update(Request $request)
    {
        $formType = $request->input('form_type', 'all');

        if ($formType === 'hero' || $formType === 'all') {
            // Validate Heroes
            for ($i = 1; $i <= 3; $i++) {
                $hero = Hero::where('position', $i)->first();
                
                $hasTitle = $request->filled("hero_{$i}_title");
                $hasImageFile = $request->hasFile("hero_{$i}_image");
                
                // Process only if user has filled title, uploaded image, or it already exists in DB
                if ($hasTitle || $hasImageFile || ($hero && $hero->image_path)) {
                    $imageRule = $hero && $hero->image_path ? 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480' : 'required|image|mimes:jpg,jpeg,png,webp|max:20480';
                    
                    $heroImageField = "hero_{$i}_image";
                    $request->validate(array_merge([
                        "hero_{$i}_title" => 'required|string|max:255',
                        $heroImageField => $imageRule,
                    ], $hasImageFile ? $this->imageCrop->cropRules($heroImageField) : []));

                    $data = [
                        'title' => $request->input("hero_{$i}_title"),
                        'position' => $i,
                    ];

                    if ($request->hasFile("hero_{$i}_image")) {
                        if ($hero && $hero->image_path) {
                            Storage::disk('public')->delete($hero->image_path);
                        }
                        $data['image_path'] = $this->imageCrop->storeCropped(
                            $request->file("hero_{$i}_image"),
                            $request,
                            "hero_{$i}_image",
                            'heroes'
                        );
                    }

                    Hero::updateOrCreate(['position' => $i], $data);
                }
            }
        }

        $definedKeys = [];
        if ($formType === 'identitas' || $formType === 'all') {
            $definedKeys = array_merge($definedKeys, [
                'site_name', 'logo', 'favicon', 'contact_phone', 'contact_email', 'contact_address', 'footer_text', 'education_fee_pdf', 'kop_surat', 'active_academic_year', 'kepala_madrasah_name', 'pimpinan_pondok_name',
            ]);
        }
        if ($formType === 'konten' || $formType === 'konten_sejarah' || $formType === 'all') {
            $definedKeys = array_merge($definedKeys, [
                'about_title', 'about_text_1', 'about_text_2', 'about_image', 'about_history_text',
            ]);
        }
        if ($formType === 'konten_kyai' || $formType === 'konten' || $formType === 'all') {
            $definedKeys = array_merge($definedKeys, [
                'about_kyai_name', 'about_kyai_text_1', 'about_kyai_text_2', 'about_kyai_image',
            ]);
        }
        if ($formType === 'login_staf' || $formType === 'all') {
            $definedKeys = array_merge($definedKeys, [
                'staff_login_image', 'staff_login_title', 'staff_login_subtitle',
            ]);
        }

        // Validate and Update Page Contents
        foreach ($definedKeys as $key) {
            $content = PageContent::where('section_key', $key)->first();

            // Cek apakah ada permintaan penghapusan gambar
            if ($request->boolean("remove_{$key}")) {
                if ($content && $content->image_path) {
                    Storage::disk('public')->delete($content->image_path);
                    $content->update(['image_path' => null]);
                }
                continue;
            }
            
            // If it's an image or file field
            if (str_ends_with($key, '_image') || $key === 'logo' || $key === 'favicon' || $key === 'education_fee_pdf' || $key === 'kop_surat') {
                $rule = match (true) {
                    $key === 'education_fee_pdf' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:2048',
                    $key === 'favicon' => 'nullable|file|mimes:ico,png,jpg,jpeg,svg|max:2048',
                    $key === 'kop_surat' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
                    default => ($content && $content->image_path ? 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:20480' : 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:20480'),
                };

                $rules = [$key => $rule];
                $skipCrop = $request->filled("{$key}_crop_skip");
                if ($key !== 'education_fee_pdf' && $key !== 'kop_surat' && $request->hasFile($key) && ! $skipCrop) {
                    $rules = array_merge($rules, $this->imageCrop->cropRules($key));
                }
                $request->validate($rules);

                if ($request->hasFile($key)) {
                    if ($content && $content->image_path) {
                        Storage::disk('public')->delete($content->image_path);
                    }

                    if ($key === 'education_fee_pdf' || $key === 'kop_surat' || $skipCrop) {
                        $filePath = $request->file($key)->store('contents', 'public');
                    } else {
                        $filePath = $this->imageCrop->storeCropped(
                            $request->file($key),
                            $request,
                            $key,
                            'contents'
                        );
                    }

                    PageContent::updateOrCreate(
                        ['section_key' => $key],
                        ['image_path' => $filePath]
                    );
                }
            } else {
                // If it's a text field (make it nullable so they can save even empty texts)
                $request->validate([
                    $key => 'nullable|string',
                ]);

                PageContent::updateOrCreate(
                    ['section_key' => $key],
                    ['content_text' => $request->input($key)]
                );
            }
        }

        \Illuminate\Support\Facades\Cache::forget('site_global_data');
        \App\Models\AdminActivityLog::log('update', "Memperbarui pengaturan situs (bagian: {$formType})");

        $redirectUrl = route('settings.index');
        if ($formType === 'login_staf') {
            $redirectUrl .= '?tab=login_staf';
        }

        return redirect()->to($redirectUrl)->with('success', 'Pengaturan situs berhasil diperbarui.');
    }
}
