@php
    $actionRoute = $actionRoute ?? route('wali.dashboard');
    $isMonitoringStaff = !auth()->user()->isSantri();
    $isArchivePeriod = isset($tahunAjaran) && $tahunAjaran && !$tahunAjaran->status_aktif;
    $displayKelas = $kelasPeriode ?? $santri?->kelas;

    $queryParams = array_filter([
        'santri_id' => ($isMonitoringStaff && isset($santri) && $santri) ? $santri->id : null,
        'tahun_ajaran_id' => $tahunAjaran?->id,
        'tingkat' => $isMonitoringStaff ? request('tingkat') : null,
        'filter_kelas_id' => $isMonitoringStaff ? request('filter_kelas_id') : null,
        'search' => $isMonitoringStaff ? request('search') : null,
    ], fn ($v) => $v !== null && $v !== '');

    $pageHeading = $pageHeading ?? match (true) {
        request()->routeIs('wali.attendance', 'santri.attendance') => 'Detail Presensi Santri',
        request()->routeIs('wali.grades', 'santri.grades') => 'Rincian Nilai & KHS',
        default => $isMonitoringStaff ? 'Pantau Akademik Santri' : 'Portal Akademik Santri',
    };

    $santriComboItems = collect($allSantrisForPreview ?? [])->map(fn ($s) => [
        'id' => $s->id,
        'nis' => (string) $s->nis,
        'name' => $s->nama_lengkap,
        'kelas' => $s->kelas->nama_kelas ?? 'Belum Diplot',
        'status' => $s->status_santri ?? 'Aktif',
        'q' => strtolower(($s->nis ?? '') . ' ' . ($s->nisn ?? '') . ' ' . ($s->nama_lengkap ?? '') . ' ' . ($s->kelas->nama_kelas ?? '')),
    ])->values()->all();

    $activeAdvancedCount = (request()->filled('tingkat') ? 1 : 0) + (request()->filled('filter_kelas_id') ? 1 : 0);
@endphp

{{-- 1. Header Konteks Halaman + Badge Read-Only (Tooltip) + Pemilih Periode Akademik Terpadu --}}
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
     x-data="{
         positionTooltipFallback(event, triggerId) {
             if (event.newState !== 'open') return;
             if (window.CSS && CSS.supports && CSS.supports('position-area', 'block-end')) return;
             const tip = event.target;
             const trigger = document.getElementById(triggerId);
             if (!tip || !trigger) return;
             requestAnimationFrame(() => {
                 const rect = trigger.getBoundingClientRect();
                 const tipRect = tip.getBoundingClientRect();
                 const vw = document.documentElement.clientWidth;
                 const vh = document.documentElement.clientHeight;
                 let left = rect.left;
                 if (left + tipRect.width > vw - 8) left = Math.max(8, vw - tipRect.width - 8);
                 let top = rect.bottom + 6;
                 if (top + tipRect.height > vh - 8 && rect.top - tipRect.height - 6 >= 8) {
                     top = rect.top - tipRect.height - 6;
                 }
                 tip.style.left = `${left}px`;
                 tip.style.top = `${top}px`;
             });
         }
     }">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            <h1 id="{{ $headingId ?? 'wali-portal-heading' }}" class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                {{ $pageHeading }}
            </h1>

            {{-- Badge Kecil Mode Pantau · Read-only dengan Interest Tooltip --}}
            <button type="button"
                    id="readonly-badge-trigger"
                    interestfor="readonly-mode-tooltip"
                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-2.5 py-0.5 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition cursor-help">
                <span class="size-1.5 rounded-full bg-primary-500" aria-hidden="true"></span>
                <span>{{ $isMonitoringStaff ? 'Mode Pantau · Read-only' : 'Read-only' }}</span>
                <svg class="size-3.5 text-gray-400 dark:text-gray-500" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </button>
            <div popover="hint"
                 id="readonly-mode-tooltip"
                 @toggle="positionTooltipFallback($event, 'readonly-badge-trigger')"
                 class="interest-tooltip rounded-xl border border-gray-200 bg-gray-900 px-3.5 py-2.5 text-xs leading-relaxed text-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                {{ $isMonitoringStaff
                    ? 'Akses pantau baca-saja (read-only) untuk meninjau kehadiran, jadwal, nilai, dan rapor santri tanpa mengubah data akademik.'
                    : 'Halaman ini bersifat baca-saja (read-only). Pembaruan presensi dan nilai dilakukan oleh ustaz/pengajar mata pelajaran.' }}
            </div>

            @if($isArchivePeriod)
                <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-semibold text-warning-800 ring-1 ring-inset ring-warning-600/25 dark:bg-warning-400/15 dark:text-warning-300">
                    <span class="size-1.5 rounded-full bg-warning-500" aria-hidden="true"></span>
                    Arsip
                </span>
            @endif
        </div>

        @if(isset($tahunAjaran) && $tahunAjaran)
            <p class="mt-0.5 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                Konteks periode:
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $tahunAjaran->nama_tahun_ajaran }} · Semester {{ $tahunAjaran->semester }}</span>
                <span class="text-gray-400 dark:text-gray-500">({{ $tahunAjaran->status_aktif ? 'Periode Aktif' : 'Data Arsip Riwayat' }})</span>
            </p>
        @endif
    </div>

    {{-- Selector Tahun Ajaran & Semester Terpadu di Dekat Heading --}}
    @if($santri && isset($availableYears) && $availableYears->count() > 0)
        <form method="GET" action="{{ $actionRoute }}" class="flex items-center gap-2 shrink-0">
            @if($isMonitoringStaff)
                <input type="hidden" name="santri_id" value="{{ $santri->id }}">
                @if(request('tingkat')) <input type="hidden" name="tingkat" value="{{ request('tingkat') }}"> @endif
                @if(request('filter_kelas_id')) <input type="hidden" name="filter_kelas_id" value="{{ request('filter_kelas_id') }}"> @endif
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @endif

            <label for="portal-tahun-ajaran" class="text-xs font-medium text-gray-500 dark:text-gray-400 shrink-0">Periode:</label>
            <select id="portal-tahun-ajaran"
                    name="tahun_ajaran_id"
                    onchange="this.form.submit()"
                    class="h-10 rounded-lg border px-3 text-xs sm:text-sm font-medium transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 {{ $isArchivePeriod ? 'border-warning-300 bg-warning-50/50 text-warning-900 dark:border-warning-700/70 dark:bg-warning-400/10 dark:text-warning-200' : 'border-gray-200 bg-white text-gray-800 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                @foreach($availableYears as $yr)
                    <option value="{{ $yr->id }}" {{ ($tahunAjaran && $tahunAjaran->id === $yr->id) ? 'selected' : '' }}>
                        {{ $yr->nama_tahun_ajaran }} · {{ $yr->semester }}{{ $yr->status_aktif ? ' (Aktif)' : ' (Arsip)' }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif
</div>

{{-- 2. Compact Neutral Santri Filter Toolbar (Satu Baris + Searchable Dropdown + Filter Lanjutan) --}}
@if($isMonitoringStaff)
    <div x-data="{
            comboOpen: false,
            advancedOpen: {{ $activeAdvancedCount > 0 ? 'true' : 'false' }},
            comboQuery: @js(request('search', '')),
            selectedId: {{ $santri?->id ?? 'null' }},
            items: @js($santriComboItems),
            totalInDb: {{ (int) ($totalMatchingSantris ?? 0) }},
            get filteredItems() {
                const q = (this.comboQuery || '').toLowerCase().trim();
                if (!q) return this.items;
                return this.items.filter(item => item.q.includes(q));
            },
            selectSantri(id) {
                this.$refs.santriIdInput.value = id;
                this.$refs.searchHiddenInput.value = '';
                this.comboOpen = false;
                this.$refs.toolbarForm.submit();
            },
            submitServerSearch() {
                this.$refs.santriIdInput.value = '';
                this.$refs.searchHiddenInput.value = this.comboQuery.trim();
                this.$refs.toolbarForm.submit();
            }
        }"
        @keydown.escape.window="comboOpen = false"
        class="mb-4 rounded-xl border border-gray-200/90 bg-white p-2.5 shadow-2xs dark:border-gray-800 dark:bg-gray-900">

        <form x-ref="toolbarForm" method="GET" action="{{ $actionRoute }}">
            @if(isset($tahunAjaran) && $tahunAjaran)
                <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaran->id }}">
            @endif
            <input x-ref="santriIdInput" type="hidden" id="portal-santri-id" name="santri_id" value="{{ $santri?->id }}">
            <input x-ref="searchHiddenInput" type="hidden" name="search" value="{{ request('search') }}">

            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                {{-- Searchable Santri Dropdown (Combobox tanpa tombol Cari) --}}
                <div class="relative flex-1 min-w-0" @click.outside="comboOpen = false">
                    <button type="button"
                            @click="comboOpen = !comboOpen; if (comboOpen) $nextTick(() => $refs.comboInput?.focus())"
                            :aria-expanded="comboOpen.toString()"
                            aria-controls="santri-combobox-panel"
                            class="flex h-10 w-full items-center justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50/60 px-3 text-left text-sm hover:bg-gray-50 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800/60 dark:hover:bg-gray-800 transition cursor-pointer">
                        <span class="flex items-center gap-2 min-w-0">
                            <svg class="size-4 shrink-0 text-gray-400 dark:text-gray-500" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            @if($santri)
                                <span class="truncate font-medium text-gray-900 dark:text-gray-100">
                                    {{ $santri->nama_lengkap }}
                                    <span class="font-normal text-gray-500 dark:text-gray-400">· NIS {{ $santri->nis }} · {{ $santri->kelas->nama_kelas ?? $santri->status_santri }}</span>
                                </span>
                            @else
                                <span class="truncate text-gray-500 dark:text-gray-400">Cari &amp; pilih santri berdasarkan nama atau NIS...</span>
                            @endif
                        </span>

                        <span class="flex items-center gap-2 shrink-0">
                            <span class="rounded-md bg-gray-200/70 px-2 py-0.5 text-[11px] font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300 tabular-nums">
                                {{ $totalMatchingSantris ?? 0 }} santri
                            </span>
                            <svg class="size-4 text-gray-400 transition-transform duration-150" :class="comboOpen ? 'rotate-180' : ''" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </button>

                    {{-- Panel Dropdown Pencarian Realtime --}}
                    <div id="santri-combobox-panel"
                         x-show="comboOpen"
                         x-cloak
                         x-transition.opacity.duration.120ms
                         class="absolute left-0 right-0 z-30 mt-1.5 rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <div class="relative">
                            <label for="santri-realtime-search" class="sr-only">Ketik nama santri, NIS, atau kelas</label>
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="size-4" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input x-ref="comboInput"
                                   id="santri-realtime-search"
                                   type="search"
                                   x-model="comboQuery"
                                   @keydown.enter.prevent="filteredItems.length === 1 ? selectSantri(filteredItems[0].id) : submitServerSearch()"
                                   autocorrect="off" autocapitalize="off" spellcheck="false"
                                   placeholder="Ketik nama, NIS, atau kelas secara realtime..."
                                   class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:bg-gray-900">
                        </div>

                        <div class="mt-1.5 flex items-center justify-between px-2 py-1 text-[11px] text-gray-500 dark:text-gray-400">
                            <span>
                                Menampilkan <strong class="font-semibold text-gray-700 dark:text-gray-200 tabular-nums" x-text="filteredItems.length"></strong> hasil pencarian
                            </span>
                            <template x-if="comboQuery.trim().length > 0 && totalInDb > items.length">
                                <button type="button" @click="submitServerSearch()" class="font-semibold text-primary-600 hover:underline dark:text-primary-400 cursor-pointer">
                                    Cari di seluruh {{ $totalMatchingSantris }} santri &rarr;
                                </button>
                            </template>
                        </div>

                        <ul role="listbox" aria-label="Daftar pilihan santri" class="mt-1 max-h-60 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-800/60">
                            <template x-for="item in filteredItems" :key="item.id">
                                <li role="option" :aria-selected="(selectedId === item.id).toString()">
                                    <button type="button"
                                            @click="selectSantri(item.id)"
                                            class="flex w-full items-center justify-between gap-3 rounded-lg px-2.5 py-2 text-left text-xs sm:text-sm hover:bg-gray-100/80 dark:hover:bg-gray-800 transition cursor-pointer"
                                            :class="selectedId === item.id ? 'bg-primary-50/70 text-primary-800 font-semibold dark:bg-primary-500/15 dark:text-primary-300' : 'text-gray-700 dark:text-gray-200'">
                                        <span class="min-w-0 truncate">
                                            <span x-text="item.name"></span>
                                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400" x-text="' · NIS ' + item.nis + ' · ' + item.kelas"></span>
                                        </span>
                                        <svg x-show="selectedId === item.id" class="size-4 shrink-0 text-primary-600 dark:text-primary-400" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </li>
                            </template>
                            <li x-show="filteredItems.length === 0" class="px-3 py-5 text-center text-xs text-gray-500 dark:text-gray-400">
                                Tidak ada santri yang cocok dengan kata kunci tersebut.
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Tombol Buka Filter Lanjutan (Tingkat & Kelas) + Reset --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button"
                            @click="advancedOpen = !advancedOpen"
                            :aria-expanded="advancedOpen.toString()"
                            aria-controls="portal-advanced-filters"
                            class="inline-flex h-10 items-center gap-1.5 rounded-lg border px-3 text-xs font-semibold transition cursor-pointer {{ $activeAdvancedCount > 0 ? 'border-primary-300 bg-primary-50/60 text-primary-800 dark:border-primary-700 dark:bg-primary-500/15 dark:text-primary-300' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                        <svg class="size-4 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter Tingkat &amp; Kelas</span>
                        @if($activeAdvancedCount > 0)
                            <span class="inline-flex size-5 items-center justify-center rounded-full bg-primary-600 text-[11px] font-bold text-white">{{ $activeAdvancedCount }}</span>
                        @endif
                    </button>

                    @if(request()->anyFilled(['tingkat', 'filter_kelas_id', 'search']))
                        <a href="{{ $actionRoute }}"
                           class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-3 text-xs font-semibold text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            {{-- Filter Lanjutan (Tingkat & Kelas) — Hanya terbuka saat dibutuhkan --}}
            <div id="portal-advanced-filters" x-show="advancedOpen" x-cloak x-collapse>
                <div class="mt-2.5 grid grid-cols-1 sm:grid-cols-2 gap-2.5 border-t border-gray-100 pt-2.5 dark:border-gray-800">
                    <div>
                        <label for="portal-tingkat" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tingkat</label>
                        <select id="portal-tingkat"
                                name="tingkat"
                                onchange="document.getElementById('portal-santri-id').value=''; this.form.submit()"
                                class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-primary-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">Semua Tingkat</option>
                            <option value="7" {{ request('tingkat') === '7' ? 'selected' : '' }}>Kelas 7 (Ula)</option>
                            <option value="8" {{ request('tingkat') === '8' ? 'selected' : '' }}>Kelas 8 (Wustho)</option>
                            <option value="9" {{ request('tingkat') === '9' ? 'selected' : '' }}>Kelas 9 (Ulya)</option>
                        </select>
                    </div>

                    <div>
                        <label for="portal-kelas" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
                        <select id="portal-kelas"
                                name="filter_kelas_id"
                                onchange="document.getElementById('portal-santri-id').value=''; this.form.submit()"
                                class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-primary-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">Semua Kelas</option>
                            @foreach($previewClasses ?? [] as $cls)
                                <option value="{{ $cls->id }}" {{ (int) request('filter_kelas_id') === $cls->id ? 'selected' : '' }}>
                                    {{ $cls->nama_kelas }} ({{ $cls->tahunAjaran->nama_tahun_ajaran ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endif

{{-- 2b. Compact Switcher Ananda (Khusus Wali Santri Multi-Anak) --}}
@if(auth()->user()->isSantri() && isset($allSantrisForPreview) && $allSantrisForPreview->count() > 1)
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 rounded-xl border border-gray-200/90 bg-white px-4 py-2.5 shadow-2xs dark:border-gray-800 dark:bg-gray-900">
        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
            <span class="font-semibold text-gray-900 dark:text-gray-100">Pilih Ananda</span>
            <span class="text-gray-400 dark:text-gray-500">· {{ $allSantrisForPreview->count() }} santri terhubung pada akun ini</span>
        </div>
        <form method="GET" action="{{ $actionRoute }}" class="flex items-center gap-2 shrink-0">
            @if(request('tahun_ajaran_id'))
                <input type="hidden" name="tahun_ajaran_id" value="{{ request('tahun_ajaran_id') }}">
            @endif
            <label for="switch_santri_id" class="sr-only">Pilih Ananda</label>
            <select id="switch_santri_id"
                    name="switch_santri_id"
                    onchange="this.form.submit()"
                    class="h-9 rounded-lg border border-gray-200 bg-gray-50/70 px-3 text-xs sm:text-sm font-semibold text-gray-800 focus:border-primary-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                @foreach($allSantrisForPreview as $sAnak)
                    <option value="{{ $sAnak->id }}" {{ ($santri && $santri->id === $sAnak->id) ? 'selected' : '' }}>
                        {{ $sAnak->nama_lengkap }} · NIS {{ $sAnak->nis }} ({{ $sAnak->kelas->nama_kelas ?? 'Santri' }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endif

{{-- 3. Neutral Student Profile Card + Contextual Navigation Tabs --}}
@if(isset($santri) && $santri)
    <div class="mb-5 rounded-xl border border-gray-200/90 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                {{-- Avatar Inisial / Foto Santri (Warna hijau teal pada avatar) --}}
                <div class="shrink-0">
                    @if($santri->foto)
                        <img src="{{ asset('storage/' . $santri->foto) }}"
                             alt="Foto {{ $santri->nama_lengkap }}"
                             class="size-14 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                    @elseif(auth()->user()->avatarUrl() && auth()->user()->isSantri())
                        <img src="{{ auth()->user()->avatarUrl() }}"
                             alt="Foto {{ $santri->nama_lengkap }}"
                             class="size-14 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                    @else
                        <div class="flex size-14 items-center justify-center rounded-xl bg-primary-50 text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-500/15 dark:text-primary-300 dark:ring-primary-400/25">
                            <span class="text-lg font-bold tracking-tight">{{ method_exists($santri, 'initials') ? $santri->initials() : strtoupper(substr($santri->nama_lengkap, 0, 2)) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Nama Santri (20–24px), Badge Status Kecil, & Metadata Sekunder --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white truncate">
                            {{ $santri->nama_lengkap }}
                        </h2>

                        @if($santri->status_santri === 'Aktif')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-semibold text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-500/15 dark:text-primary-300 dark:ring-primary-400/25">
                                <span class="size-1.5 rounded-full bg-primary-500" aria-hidden="true"></span>
                                Aktif
                            </span>
                        @elseif($santri->status_santri === 'Lulus')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-semibold text-warning-800 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-400/15 dark:text-warning-300">
                                <span class="size-1.5 rounded-full bg-warning-500" aria-hidden="true"></span>
                                Alumni / Lulus
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $santri->status_santri }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-1 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        <span>NIS <strong class="font-mono font-medium text-gray-700 dark:text-gray-300">{{ $santri->nis }}</strong></span>
                        <span aria-hidden="true" class="text-gray-300 dark:text-gray-700">·</span>
                        <span>Kelas <strong class="font-medium text-gray-700 dark:text-gray-300">{{ $displayKelas->nama_kelas ?? 'Belum Diplot' }}</strong></span>
                        @if($santri->nama_wali)
                            <span aria-hidden="true" class="text-gray-300 dark:text-gray-700">·</span>
                            <span>Wali: <strong class="font-medium text-gray-700 dark:text-gray-300">{{ $santri->nama_wali }}</strong></span>
                        @endif
                        @if($displayKelas?->waliKelas)
                            <span aria-hidden="true" class="text-gray-300 dark:text-gray-700">·</span>
                            <span>Wali Kelas: <strong class="font-medium text-gray-700 dark:text-gray-300">{{ $displayKelas->waliKelas->nama_lengkap }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Primary Action: Unduh Rapor PDF (Hijau Teal) --}}
            <div class="w-full sm:w-auto shrink-0">
                <a href="{{ route('wali.rapor-pdf', $queryParams) }}"
                   target="_blank"
                   class="inline-flex h-10 w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 px-4 text-xs sm:text-sm font-semibold text-white shadow-xs transition">
                    <svg class="size-4 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh Rapor{{ isset($tahunAjaran) && $tahunAjaran ? ' (' . $tahunAjaran->semester . ')' : '' }}
                </a>
            </div>
        </div>

        {{-- Contextual Sub-Navigation (Menggantikan panel hijau Read-only & tombol terpisah) --}}
        <nav aria-label="Menu Pemantauan Akademik Santri"
             class="flex items-center gap-5 overflow-x-auto border-t border-gray-100 bg-gray-50/40 px-4 sm:px-5 dark:border-gray-800/80 dark:bg-gray-900/60">
            <a href="{{ route('wali.dashboard', $queryParams) }}"
               @if(request()->routeIs('wali.dashboard', 'santri.dashboard')) aria-current="page" @endif
               class="-mb-px inline-flex items-center gap-1.5 border-b-2 py-2.5 text-xs sm:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('wali.dashboard', 'santri.dashboard') ? 'border-primary-600 text-primary-700 dark:border-primary-400 dark:text-primary-300' : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="size-4 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                Ringkasan &amp; Jadwal
            </a>

            <a href="{{ route('wali.attendance', $queryParams) }}"
               @if(request()->routeIs('wali.attendance', 'santri.attendance')) aria-current="page" @endif
               class="-mb-px inline-flex items-center gap-1.5 border-b-2 py-2.5 text-xs sm:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('wali.attendance', 'santri.attendance') ? 'border-primary-600 text-primary-700 dark:border-primary-400 dark:text-primary-300' : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="size-4 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Detail Presensi
            </a>

            <a href="{{ route('wali.grades', $queryParams) }}"
               @if(request()->routeIs('wali.grades', 'santri.grades')) aria-current="page" @endif
               class="-mb-px inline-flex items-center gap-1.5 border-b-2 py-2.5 text-xs sm:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('wali.grades', 'santri.grades') ? 'border-primary-600 text-primary-700 dark:border-primary-400 dark:text-primary-300' : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200' }}">
                <svg class="size-4 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Rincian Nilai
            </a>
        </nav>
    </div>
@endif

@once
    <script type="module">
        if (!("popover" in HTMLElement.prototype)) {
            import("https://unpkg.com/@oddbird/popover-polyfill@latest/dist/popover.min.js");
        }
        if (!HTMLButtonElement.prototype.hasOwnProperty("interestForElement")) {
            import("https://unpkg.com/interestfor@latest");
        }
    </script>
@endonce
