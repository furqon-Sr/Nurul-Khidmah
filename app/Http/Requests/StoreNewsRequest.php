<?php

namespace App\Http\Requests;

use App\Services\ImageCropService;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            // Allow jpg, jpeg, png, webp, svg formats, max 5MB (5120KB)
            'main_image_path' => 'required|file|mimes:jpg,jpeg,png,webp,svg|max:20480',
        ], app(ImageCropService::class)->cropRules('main_image_path'));
    }

    /**
     * Custom validation messages for image upload.
     */
    public function messages(): array
    {
        return [
            'main_image_path.max' => 'File terlalu besar',
            'main_image_path.mimes' => 'Format tidak didukung',
        ];
    }
}
