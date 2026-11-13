<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationProgram;
use App\Models\PageContent;
use App\Support\EducationContentFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EducationFeeController extends Controller
{
    public function index(): View
    {
        $programs = EducationProgram::orderBy('order')->orderBy('name')->get();

        $docKeys = array_merge(
            ['education_fee_pdf'],
            $programs->map(fn ($p) => 'education_fee_file_'.$p->slug)->all()
        );

        $docs = PageContent::whereIn('section_key', $docKeys)->get()->keyBy('section_key');

        $uploadedDocuments = [];
        $globalDoc = $docs->get('education_fee_pdf');
        if ($globalDoc && $globalDoc->image_path) {
            $preview = EducationContentFormatter::buildFeeDocumentPreview(
                $globalDoc->image_path,
                $globalDoc->content_text ?: 'Semua Program / Umum',
                route('education.download-fee')
            );
            if ($preview) {
                $uploadedDocuments[] = [
                    'section_key' => 'education_fee_pdf',
                    'target_label' => 'Umum (Semua Program Pendidikan)',
                    'preview' => $preview,
                ];
            }
        }

        foreach ($programs as $program) {
            $key = 'education_fee_file_'.$program->slug;
            $doc = $docs->get($key);
            if ($doc && $doc->image_path) {
                $preview = EducationContentFormatter::buildFeeDocumentPreview(
                    $doc->image_path,
                    $doc->content_text ?: $program->name,
                    route('education.download-fee', ['program' => $program->slug])
                );
                if ($preview) {
                    $uploadedDocuments[] = [
                        'section_key' => $key,
                        'target_label' => $program->name,
                        'preview' => $preview,
                    ];
                }
            }
        }

        return view('admin.fees.index', compact('programs', 'uploadedDocuments'));
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_program' => ['required', 'string', 'max:120'],
            'fee_document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf,csv,txt', 'max:5120'],
        ], [
            'fee_document.required' => 'Silakan pilih file biaya pendaftaran (Gambar, CSV, atau PDF).',
            'fee_document.mimes' => 'Format file harus berupa Gambar (JPG, PNG, WEBP), CSV, atau PDF.',
            'fee_document.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $target = trim($validated['target_program']);
        $sectionKey = $target === 'global'
            ? 'education_fee_pdf'
            : 'education_fee_file_'.$target;

        if ($target !== 'global' && ! EducationProgram::where('slug', $target)->exists()) {
            return back()->withErrors(['target_program' => 'Program pendidikan yang dipilih tidak ditemukan.']);
        }

        $content = PageContent::firstOrNew(['section_key' => $sectionKey]);
        if ($content->image_path && Storage::disk('public')->exists($content->image_path)) {
            Storage::disk('public')->delete($content->image_path);
        }

        $file = $request->file('fee_document');
        $path = $file->store('contents', 'public');

        $content->image_path = $path;
        $content->content_text = $file->getClientOriginalName();
        $content->save();

        Cache::forget('site_global_data');

        return redirect()->route('admin.fees.index')
            ->with('success', 'Dokumen biaya pendaftaran ('.$file->getClientOriginalName().') berhasil diunggah dan langsung tampil di halaman pendidikan.');
    }

    public function destroyDocument(string $sectionKey): RedirectResponse
    {
        if ($sectionKey !== 'education_fee_pdf' && ! str_starts_with($sectionKey, 'education_fee_file_')) {
            abort(404);
        }

        $content = PageContent::where('section_key', $sectionKey)->first();
        if ($content) {
            if ($content->image_path && Storage::disk('public')->exists($content->image_path)) {
                Storage::disk('public')->delete($content->image_path);
            }
            $content->delete();
            Cache::forget('site_global_data');
        }

        return redirect()->route('admin.fees.index')
            ->with('success', 'Dokumen biaya pendaftaran berhasil dihapus.');
    }
}
