<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Services\ImageCropService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function __construct(protected ImageCropService $imageCrop) {}

    public function index()
    {
        $news = News::latest()->paginate(10);
        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function store(StoreNewsRequest $request)
    {
        $validated = $request->validated();
        
        if (empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Handle image cropping and storage with error logging
        if ($request->hasFile('main_image_path')) {
            try {
                $validated['main_image_path'] = $this->imageCrop->storeCropped(
                    $request->file('main_image_path'),
                    $request,
                    'main_image_path',
                    'news'
                );
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                return back()->withErrors(['main_image_path' => $e->getMessage()]);
            }
        }

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $count = 1;
        while (News::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        $validated['slug'] = $slug;

        News::create($validated);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    public function update(UpdateNewsRequest $request, News $news)
    {
        $validated = $request->validated();
        
        if (empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        if ($request->hasFile('main_image_path')) {
            if ($news->main_image_path && Storage::disk('public')->exists($news->main_image_path)) {
                Storage::disk('public')->delete($news->main_image_path);
            }
            try {
                $validated['main_image_path'] = $this->imageCrop->storeCropped(
                    $request->file('main_image_path'),
                    $request,
                    'main_image_path',
                    'news'
                );
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                return back()->withErrors(['main_image_path' => $e->getMessage()]);
            }
        }

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $count = 1;
        while (News::where('slug', $slug)->where('id', '!=', $news->id)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        $validated['slug'] = $slug;

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(News $news)
    {
        if ($news->main_image_path && Storage::disk('public')->exists($news->main_image_path)) {
            Storage::disk('public')->delete($news->main_image_path);
        }
        $news->delete();
        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil dihapus.');
    }
}
