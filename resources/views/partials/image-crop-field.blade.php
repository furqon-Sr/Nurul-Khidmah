{{--
    Field upload gambar dengan modal Cropper.js.
    Params: name, label, aspectRatio (float, kosong = bebas), aspectLabel, required, hint, accept, currentImageUrl (opsional)
--}}
@php
    $name = $name ?? 'image';
    $aspectRatio = $aspectRatio ?? null;
    $aspectLabel = $aspectLabel ?? ($aspectRatio ? 'Rasio tetap' : 'Bebas (seret sudut crop)');
    $required = $required ?? false;
    $accept = $accept ?? 'image/jpeg,image/png,image/jpg,image/webp';
    $hint = $hint ?? 'Format: JPG, PNG, WEBP. Maks. 2MB. Setelah memilih file, modal crop akan terbuka.';
@endphp
<div class="image-crop-wrapper space-y-2" data-field="{{ $name }}">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">
        {{ $label }}
        @if($required)<span class="text-red-500">*</span>@endif
    </label>
    @if(!empty($hint))
        <p class="text-xs text-gray-600 dark:text-slate-400">{{ $hint }}</p>
        <p class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">Rasio disarankan: {{ $aspectLabel }}</p>
    @endif

    @if(!empty($currentImageUrl))
        <p class="text-xs text-gray-600 dark:text-slate-400">Gambar saat ini:</p>
        <img src="{{ $currentImageUrl }}" alt="" class="max-h-40 rounded-lg border border-gray-200 dark:border-slate-600 object-cover mb-2">
    @endif

    <input type="file"
           name="{{ $name }}"
           id="{{ $name }}"
           accept="{{ $accept }}"
           @if($required) required @endif
           class="image-crop-input w-full text-sm text-gray-600 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-green-700 hover:file:bg-green-100 dark:file:text-slate-200 dark:hover:file:bg-green-600 border border-gray-300 dark:border-slate-700 p-2 rounded-md"
           data-aspect-ratio="{{ $aspectRatio ?? '' }}"
           data-aspect-label="{{ $aspectLabel }}">

    <input type="hidden" name="{{ $name }}_crop_x" value="{{ old($name.'_crop_x') }}">
    <input type="hidden" name="{{ $name }}_crop_y" value="{{ old($name.'_crop_y') }}">
    <input type="hidden" name="{{ $name }}_crop_width" value="{{ old($name.'_crop_width') }}">
    <input type="hidden" name="{{ $name }}_crop_height" value="{{ old($name.'_crop_height') }}">
    <input type="hidden" name="{{ $name }}_cropped_data" value="{{ old($name.'_cropped_data') }}">

    <div class="image-crop-status hidden rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 p-3">
        <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 mb-2">✓ Area crop sudah dipilih</p>
        <img src="" alt="Pratinjau hasil crop" class="image-crop-result-preview max-h-48 rounded-md border border-emerald-200 dark:border-emerald-700 object-contain bg-white dark:bg-slate-900">
    </div>

    @error($name) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    @error($name.'_crop_width') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    @error($name.'_cropped_data') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>
