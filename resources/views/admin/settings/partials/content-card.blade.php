{{--
    REUSABLE CONTENT CARD COMPONENT
    ================================
    Usage:
    @include('admin.settings.partials.content-card', [
        'cardId'      => 'about_history',          // unique ID for this card form
        'formType'    => 'konten_sejarah',          // matches controller's $formType
        'icon'        => '📖',                     // emoji or SVG
        'title'       => 'Sejarah Pesantren',
        'description' => 'Teks ini muncul di...',
        'fields'      => [
            ['key' => 'about_title',        'label' => 'Judul',       'type' => 'text'],
            ['key' => 'about_text_1',       'label' => 'Paragraf 1',  'type' => 'rte'],
            ['key' => 'about_image',        'label' => 'Gambar',      'type' => 'image'],
        ],
        'contents'    => $contents,
    ])
--}}

<div id="card-{{ $cardId }}"
     class="bg-white dark:bg-slate-900 shadow-sm border border-gray-100 dark:border-slate-700 rounded-2xl overflow-hidden mb-6 transition-all">

    {{-- Card Header --}}
    <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-green-50 to-white dark:from-slate-800 dark:to-slate-950 border-b border-gray-100 dark:border-slate-700">
        <div class="flex items-center gap-3">
            <span class="text-2xl">{{ $icon }}</span>
            <div>
                <h3 class="font-bold text-lg text-green-800 leading-tight">{{ $title }}</h3>
                @if(!empty($description))
                    <p class="text-xs text-gray-500 mt-0.5">{{ $description }}</p>
                @endif
            </div>
        </div>
        {{-- Collapse toggle --}}
        <button type="button"
                onclick="toggleCard('{{ $cardId }}')"
                id="toggle-btn-{{ $cardId }}"
                class="text-gray-400 hover:text-green-700 transition p-1 rounded-lg hover:bg-green-50">
            <svg id="icon-down-{{ $cardId }}" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <svg id="icon-up-{{ $cardId }}" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
    </div>

    {{-- Card Body --}}
    <div id="body-{{ $cardId }}">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="form_type" value="{{ $formType }}">

            <div class="px-6 py-5 space-y-5">
                @foreach($fields as $field)
                    @php
                        $key   = $field['key'];
                        $type  = $field['type'];
                        $label = $field['label'];
                        $hint  = $field['hint'] ?? null;
                        $currentText  = old($key, $contents[$key]->content_text ?? '');
                        $currentImage = $contents[$key]->image_path ?? null;
                    @endphp

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-200 mb-1.5">
                            {{ $label }}
                            @if($type === 'image') <span class="text-gray-400 dark:text-slate-400 font-normal">(gambar)</span> @endif
                        </label>

                        @if($hint)
                            <p class="text-xs text-gray-400 dark:text-slate-400 mb-2">{{ $hint }}</p>
                        @endif

                        {{-- TEXT INPUT --}}
                        @if($type === 'text')
                            <input type="text" name="{{ $key }}" value="{{ $currentText }}"
                                   class="w-full rounded-xl border-gray-200 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-400 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm">

                        {{-- RICH TEXT EDITOR (Quill) --}}
                        @elseif($type === 'rte')
                            <div id="quill-{{ $cardId }}-{{ $key }}"
                                 class="quill-editor-container rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-950 min-h-[140px] text-sm text-gray-900 dark:text-slate-100"
                                 data-content="{{ $currentText }}"></div>
                            <input type="hidden" name="{{ $key }}" id="hidden-{{ $cardId }}-{{ $key }}" value="{{ $currentText }}">

                        {{-- PLAIN TEXTAREA --}}
                        @elseif($type === 'textarea')
                            <textarea name="{{ $key }}" rows="3"
                                      class="w-full rounded-xl border-gray-200 shadow-sm focus:border-green-500 focus:ring-green-400 text-sm resize-none">{{ $currentText }}</textarea>

                        {{-- IMAGE UPLOAD + CROP --}}
                        @elseif($type === 'image')
                            @include('partials.image-crop-field', [
                                'name' => $key,
                                'label' => $label,
                                'required' => false,
                                'aspectRatio' => $field['aspectRatio'] ?? (str_contains($key, 'kyai') ? 3/4 : 4/3),
                                'aspectLabel' => $field['aspectLabel'] ?? (str_contains($key, 'kyai') ? '3:4 (foto portrait)' : '4:3'),
                                'currentImageUrl' => $currentImage ? asset('storage/' . $currentImage) : null,
                            ])
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Card Footer / Save Button --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-100 dark:border-slate-700 flex justify-between items-center">
                <p class="text-xs text-gray-400 dark:text-slate-400">Perubahan hanya pada bagian ini yang akan disimpan.</p>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0D6B3A] hover:bg-green-800 text-white text-sm font-semibold rounded-xl shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan {{ $title }}
                </button>
            </div>
        </form>
    </div>
</div>