<?php

namespace App\Http\Controllers;

use App\Models\EducationProgram;
use App\Models\PageContent;
use App\Support\EducationContentFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EducationController extends Controller
{
    /**
     * Halaman ringkasan / pilihan program pendidikan & preview dokumen biaya.
     */
    public function index(): View
    {
        $programs = EducationProgram::active()->orderBy('order')->orderBy('name')->get();

        $globalDoc = PageContent::where('section_key', 'education_fee_pdf')->first();
        $globalFeePreview = EducationContentFormatter::buildFeeDocumentPreview(
            $globalDoc?->image_path,
            $globalDoc?->content_text ? 'Umum (Semua Program) — '.$globalDoc->content_text : 'Dokumen Biaya Pendidikan',
            route('education.download-fee')
        );

        // Kumpulkan dokumen biaya khusus per program jika ada
        $programFeeKeys = $programs->map(fn ($p) => 'education_fee_file_'.$p->slug)->all();
        $programDocs = $programFeeKeys !== []
            ? PageContent::whereIn('section_key', $programFeeKeys)->get()->keyBy('section_key')
            : collect();

        $programFeePreviews = [];
        foreach ($programs as $program) {
            $doc = $programDocs->get('education_fee_file_'.$program->slug);
            $preview = EducationContentFormatter::buildFeeDocumentPreview(
                $doc?->image_path,
                $doc?->content_text ? $program->name.' — '.$doc->content_text : 'Biaya '.$program->name,
                route('education.download-fee', ['program' => $program->slug])
            );
            if ($preview) {
                $programFeePreviews[$program->id] = $preview;
            }
        }

        return view('education', compact(
            'programs',
            'globalFeePreview',
            'programFeePreviews'
        ));
    }

    /**
     * Halaman tersendiri untuk setiap program pendidikan yang diaktifkan admin.
     */
    public function show(EducationProgram $program): View
    {
        abort_unless($program->is_active, 404);

        $programs = EducationProgram::active()->orderBy('order')->orderBy('name')->get();
        $parsedDescription = EducationContentFormatter::formatDescription($program->description);
        $ctaLabel = EducationContentFormatter::registrationCtaLabel($program->name);

        // Cek file biaya khusus program ini, fallback ke dokumen biaya global
        $programDoc = PageContent::where('section_key', 'education_fee_file_'.$program->slug)->first();
        $globalDoc = PageContent::where('section_key', 'education_fee_pdf')->first();

        $feePreview = EducationContentFormatter::buildFeeDocumentPreview(
            $programDoc?->image_path,
            $programDoc?->content_text ?: 'Biaya Pendaftaran '.$program->name,
            route('education.download-fee', ['program' => $program->slug])
        );

        $globalFeePreview = EducationContentFormatter::buildFeeDocumentPreview(
            $globalDoc?->image_path,
            $globalDoc?->content_text ?: 'Dokumen Biaya Pendidikan',
            route('education.download-fee')
        );

        $activeFeePreview = $feePreview ?? $globalFeePreview;

        return view('education-show', compact(
            'program',
            'programs',
            'parsedDescription',
            'ctaLabel',
            'activeFeePreview'
        ));
    }

    /**
     * Unduh file biaya pendidikan (mendukung parameter ?program=slug atau dokumen umum).
     */
    public function downloadFee(Request $request)
    {
        $programSlug = $request->query('program');
        $doc = null;

        if (is_string($programSlug) && $programSlug !== '') {
            $doc = PageContent::where('section_key', 'education_fee_file_'.$programSlug)->first();
        }

        if (! $doc || ! $doc->image_path || ! Storage::disk('public')->exists($doc->image_path)) {
            $doc = PageContent::where('section_key', 'education_fee_pdf')->first();
        }

        if ($doc && $doc->image_path && Storage::disk('public')->exists($doc->image_path)) {
            $ext = pathinfo($doc->image_path, PATHINFO_EXTENSION) ?: 'pdf';
            $base = $programSlug ? 'Biaya_Pendidikan_'.ucfirst($programSlug) : 'Biaya_Pendidikan_Nurul_Khidmah';

            return Storage::disk('public')->download($doc->image_path, $base.'.'.$ext);
        }

        return abort(404, 'Dokumen biaya pendidikan belum diunggah.');
    }
}
