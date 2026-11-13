{{-- Partial preview dokumen biaya (Gambar, CSV, atau PDF) --}}
@props(['preview'])

@if(! empty($preview))
<div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50/80 px-5 py-4">
        <div class="min-w-0 flex items-center gap-3">
            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold uppercase tracking-wider
                {{ $preview['type'] === 'image' ? 'bg-emerald-100 text-emerald-800' : ($preview['type'] === 'csv' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                {{ $preview['ext'] }}
            </span>
            <h3 class="truncate text-base md:text-lg font-bold text-gray-900">
                {{ $preview['label'] }}
            </h3>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(in_array($preview['type'], ['image', 'pdf'], true))
                <a href="{{ $preview['url'] }}" target="_blank" rel="noopener"
                   class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Buka Penuh
                </a>
            @endif
            <a href="{{ $preview['download_url'] }}"
               class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-primary-600 px-4 text-sm font-bold text-white hover:bg-primary-700 transition shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh ({{ $preview['ext'] }})
            </a>
        </div>
    </div>

    <div class="p-4 sm:p-6">
        @if($preview['type'] === 'image')
            <div class="flex justify-center rounded-xl bg-gray-50 p-3 border border-gray-100">
                <a href="{{ $preview['url'] }}" target="_blank" rel="noopener" title="Klik untuk memperbesar gambar">
                    <img src="{{ $preview['url'] }}"
                         alt="{{ $preview['label'] }}"
                         class="max-h-[680px] w-auto rounded-lg object-contain shadow-xs">
                </a>
            </div>
        @elseif($preview['type'] === 'pdf')
            <div class="space-y-3">
                <iframe src="{{ $preview['url'] }}#view=FitH"
                        title="Pratinjau {{ $preview['label'] }}"
                        class="w-full h-[540px] md:h-[680px] rounded-xl border border-gray-200 bg-gray-50"></iframe>
                <p class="text-sm text-gray-600 text-left">
                    Jika pratinjau PDF tidak tampil pada perangkat Anda, silakan klik <a href="{{ $preview['download_url'] }}" class="font-bold text-primary-700 underline">Unduh Dokumen PDF</a>.
                </p>
            </div>
        @elseif($preview['type'] === 'csv')
            @if(! empty($preview['csv_headers']))
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-left">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach($preview['csv_headers'] as $header)
                                    <th scope="col" class="px-4 py-3 text-sm md:text-base font-bold text-gray-800 whitespace-nowrap">
                                        {{ $header }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($preview['csv_rows'] as $row)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    @foreach($row as $cell)
                                        <td class="px-4 py-3 text-base leading-[1.6] text-gray-700">
                                            {{ $cell !== '' ? $cell : '—' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($preview['csv_headers']) }}" class="px-4 py-6 text-center text-base text-gray-500">
                                        File CSV tidak berisi baris data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-base text-gray-600 text-left">File CSV kosong atau tidak dapat dibaca. Silakan unduh file untuk melihat isinya.</p>
            @endif
        @else
            <p class="text-base text-gray-600 text-left">Berkas terlampir siap diunduh melalui tombol di atas.</p>
        @endif
    </div>
</div>
@endif
