<?php

namespace App\Http\Requests;

use App\Services\ImageCropService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon_or_image_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ], app(ImageCropService::class)->cropRules('icon_or_image_path'));
    }
}
