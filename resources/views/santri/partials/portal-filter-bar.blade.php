@php
    $actionRoute = $actionRoute ?? route('wali.dashboard');
    $currentFilterParams = array_filter([
        'santri_id' => $santri?->id,
        'tahun_ajaran_id' => $tahunAjaran?->id,
        'tingkat' => request('tingkat'),
        'filter_kelas_id' => request('filter_kelas_id'),
        'search' => request('search'),
    ], fn ($v) => $v !== null && $v !== '');
@endphp

{{-- 1. Bar Filter & Pencarian Cepat Mode Pantau Pengurus Akademik (Mendukung Ratusan / Ribuan Santri) --}}
@if(auth()->user()->isPengurusAkademik() || auth()->user()->isKepalaSekolah() || auth()->user()->isPimpinan() || auth()->user()->isSuperAdmin())
    <div class="mb-5 p-4 sm:p-5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">
                    Mode Pantau Portal Wali — Filterisasi Cepat Santri
                </p>
                <p class="text-xs text-indigo-900/80 dark:text-indigo-200/80">
                    Saring berdasarkan Tingkat (7 / 8 / 9), Kelas, atau ketik Nama / NIS santri ({{ $totalMatchingSantris ?? 0 }} santri cocok).
                </p>
            </div>
            @if(request()->anyFilled(['tingkat', 'filter_kelas_id', 'search']))
                <a href="{{ $actionRoute }}" class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:underline">
                    Reset Filter Santri
                </a>
            @endif
        </div>

        <form method="GET" action="{{ $actionRoute }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            @if(isset($tahunAjaran) && $tahunAjaran)
                <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaran->id }}">
            @endif

            <div class="lg:col-span-2">
                <label for="portal-tingkat" class="block text-[11px] font-bold uppercase text-indigo-800 dark:text-indigo-200 mb-1">Tingkat</label>
                <select id="portal-tingkat" name="tingkat" onchange="const s = document.getElementById('portal-santri-id'); if(s) s.value=''; this.form.submit()"
                        class="w-full min-h-11 px-3 py-2 text-sm rounded-xl border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                    <option value="">Semua Tingkat</option>
                    <option value="7" {{ request('tingkat') === '7' ? 'selected' : '' }}>Kelas 7 (Ula)</option>
                    <option value="8" {{ request('tingkat') === '8' ? 'selected' : '' }}>Kelas 8 (Wustho)</option>
                    <option value="9" {{ request('tingkat') === '9' ? 'selected' : '' }}>Kelas 9 (Ulya)</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="portal-kelas" class="block text-[11px] font-bold uppercase text-indigo-800 dark:text-indigo-200 mb-1">Filter Kelas</label>
                <select id="portal-kelas" name="filter_kelas_id" onchange="const s = document.getElementById('portal-santri-id'); if(s) s.value=''; this.form.submit()"
                        class="w-full min-h-11 px-3 py-2 text-sm rounded-xl border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                    <option value="">Semua Kelas</option>
                    @foreach($previewClasses ?? [] as $cls)
                        <option value="{{ $cls->id }}" {{ (int) request('filter_kelas_id') === $cls->id ? 'selected' : '' }}>
                            {{ $cls->nama_kelas }} ({{ $cls->tahunAjaran->nama_tahun_ajaran ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label for="portal-search" class="block text-[11px] font-bold uppercase text-indigo-800 dark:text-indigo-200 mb-1">Cari Nama / NIS</label>
                <div class="flex gap-1.5">
                    <input type="text" id="portal-search" name="search" value="{{ request('search') }}"
                           autocorrect="off" autocapitalize="off" spellcheck="false"
                           onkeydown="if(event.key === 'Enter') { const s = document.getElementById('portal-santri-id'); if(s) s.value = ''; }"
                           placeholder="Nama / NIS..."
                           class="w-full min-h-11 px-3 py-2 text-sm rounded-xl border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                    <button type="submit" onclick="const s = document.getElementById('portal-santri-id'); if(s) s.value = '';" class="min-h-11 px-3.5 py-2 rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-bold shrink-0">
                        Cari
                    </button>
                </div>
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <label for="portal-santri-id" class="block text-[11px] font-bold uppercase text-indigo-800 dark:text-indigo-200 mb-1">Pilih Santri Hasil Filter</label>
                <select id="portal-santri-id" name="santri_id" onchange="this.form.submit()"
                        class="w-full min-h-11 px-3 py-2 text-sm font-semibold rounded-xl border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                    @forelse($allSantrisForPreview ?? [] as $item)
                        <option value="{{ $item->id }}" {{ ($santri && $santri->id === $item->id) ? 'selected' : '' }}>
                            {{ $item->nis }} — {{ $item->nama_lengkap }} ({{ $item->kelas->nama_kelas ?? $item->status_santri }})
                        </option>
                    @empty
                        <option value="">Tidak ada santri cocok</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>
@endif

{{-- 1b. Bar Switcher Pilihan Ananda (Bagi Wali Santri yang Memiliki Lebih dari 1 Anak Terdaftar) --}}
@if(auth()->user()->isSantri() && isset($allSantrisForPreview) && $allSantrisForPreview->count() > 1)
    <div class="mb-5 p-4 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                👨‍👩‍👧‍👦
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-teal-900 dark:text-teal-200">
                    Pilih Ananda yang Dipantau ({{ $allSantrisForPreview->count() }} Santri Terhubung)
                </p>
                <p class="text-xs text-teal-700 dark:text-teal-300">
                    Beralih pantau data nilai, absensi, dan rapor putra/putri Anda secara langsung.
                </p>
            </div>
        </div>
        <form method="GET" action="{{ $actionRoute }}" class="flex items-center gap-2 shrink-0">
            @if(request('tahun_ajaran_id'))
                <input type="hidden" name="tahun_ajaran_id" value="{{ request('tahun_ajaran_id') }}">
            @endif
            <label for="switch_santri_id" class="text-xs font-bold text-teal-900 dark:text-teal-200 shrink-0">Ananda:</label>
            <select id="switch_santri_id" name="switch_santri_id" onchange="this.form.submit()"
                    class="min-h-10 px-3 py-1.5 text-xs sm:text-sm font-bold rounded-xl border border-teal-300 dark:border-teal-700 bg-white dark:bg-gray-900 text-teal-950 dark:text-teal-100 shadow-sm focus:ring-2 focus:ring-teal-500">
                @foreach($allSantrisForPreview as $sAnak)
                    <option value="{{ $sAnak->id }}" {{ ($santri && $santri->id === $sAnak->id) ? 'selected' : '' }}>
                        {{ $sAnak->nama_lengkap }} (NIS: {{ $sAnak->nis }}) — {{ $sAnak->kelas->nama_kelas ?? 'Santri' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endif

{{-- 2. Pemilih Arsip Tahun Ajaran / Semester (Bagi Wali Santri maupun Pengurus Akademik) --}}
@if($santri && isset($availableYears) && $availableYears->count() > 0)
    <div class="mb-6 p-3.5 sm:px-5 rounded-2xl bg-white dark:bg-gray-800 border border-teal-200 dark:border-teal-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="text-xs sm:text-sm text-gray-700 dark:text-gray-200">
            <span class="font-bold text-teal-800 dark:text-teal-300">📅 Periode Akademik / Arsip Rapor:</span>
            Menampilkan data kelas <strong>{{ $kelasPeriode->nama_kelas ?? $santri->kelas->nama_kelas ?? '-' }}</strong>
            @if($santri->status_santri === 'Lulus')
                <span class="ml-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">Alumni / Lulus</span>
            @endif
        </div>

        <form method="GET" action="{{ $actionRoute }}" class="flex items-center gap-2">
            @if(auth()->user()->isPengurusAkademik())
                <input type="hidden" name="santri_id" value="{{ $santri->id }}">
                @if(request('tingkat')) <input type="hidden" name="tingkat" value="{{ request('tingkat') }}"> @endif
                @if(request('filter_kelas_id')) <input type="hidden" name="filter_kelas_id" value="{{ request('filter_kelas_id') }}"> @endif
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @endif

            <label for="portal-tahun-ajaran" class="text-xs font-semibold text-gray-600 dark:text-gray-300 shrink-0">Semester:</label>
            <select id="portal-tahun-ajaran" name="tahun_ajaran_id" onchange="this.form.submit()"
                    class="min-h-10 px-3 py-1.5 text-xs sm:text-sm font-bold rounded-xl border border-teal-300 dark:border-teal-700 bg-teal-50/60 dark:bg-gray-900 text-teal-950 dark:text-teal-200">
                @foreach($availableYears as $yr)
                    <option value="{{ $yr->id }}" {{ ($tahunAjaran && $tahunAjaran->id === $yr->id) ? 'selected' : '' }}>
                        {{ $yr->nama_tahun_ajaran }} — {{ $yr->semester }}{{ $yr->status_aktif ? ' (Aktif)' : ' (Arsip)' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endif
