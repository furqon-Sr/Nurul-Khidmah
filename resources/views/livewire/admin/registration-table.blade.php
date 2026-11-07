<div x-data="{
        statusTarget: { id: null, name: '', regNo: '', status: 'pending' },
        newStatus: 'pending',
        deleteTarget: { id: null, name: '', regNo: '' },
        bulkTargetAction: '',
        bulkTargetLabel: '',
        openStatusModal(id, name, regNo, currentStatus) {
            this.statusTarget = { id, name, regNo, status: currentStatus };
            this.newStatus = currentStatus === 'approved' ? 'accepted' : currentStatus;
            this.$refs.statusDialog.showModal();
        },
        openDeleteModal(id, name, regNo) {
            this.deleteTarget = { id, name, regNo };
            this.$refs.deleteDialog.showModal();
        },
        openBulkModal(action, label) {
            this.bulkTargetAction = action;
            this.bulkTargetLabel = label;
            this.$refs.bulkDialog.showModal();
        },
        positionFallbackPopover(event, triggerId) {
            if (event.newState !== 'open') return;
            if (window.CSS && CSS.supports && CSS.supports('position-area', 'block-end')) return;
            const panel = event.target;
            const trigger = document.getElementById(triggerId);
            if (!panel || !trigger) return;
            requestAnimationFrame(() => {
                const rect = trigger.getBoundingClientRect();
                const panelRect = panel.getBoundingClientRect();
                const vw = document.documentElement.clientWidth;
                const vh = document.documentElement.clientHeight;
                let left = rect.right - panelRect.width;
                if (left < 8) left = 8;
                if (left + panelRect.width > vw - 8) left = Math.max(8, vw - panelRect.width - 8);
                let top = rect.bottom + 6;
                if (top + panelRect.height > vh - 8 && rect.top - panelRect.height - 6 >= 8) {
                    top = rect.top - panelRect.height - 6;
                }
                panel.style.left = `${left}px`;
                panel.style.top = `${top}px`;
            });
        }
    }">

    {{-- Feedback Notifikasi Aksi Livewire --}}
    @if(session('livewire_success'))
        <div role="status" class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-success-200 bg-success-50/80 px-4 py-2.5 text-xs sm:text-sm font-medium text-success-800 dark:border-success-800/60 dark:bg-success-950/40 dark:text-success-300">
            <div class="flex items-center gap-2">
                <svg class="size-4 shrink-0 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('livewire_success') }}</span>
            </div>
        </div>
    @endif

    {{-- 1. KPI Cards: Netral, seragam, warna status hanya pada icon/badge/indikator kecil, tinggi ~102px (95–110px) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">
        {{-- Card Total Pendaftar --}}
        <button type="button"
                wire:click="setStatusFilter('')"
                aria-pressed="{{ empty($status) ? 'true' : 'false' }}"
                class="group flex h-[102px] w-full flex-col justify-between rounded-xl border px-4 py-3.5 text-left transition-colors cursor-pointer bg-white dark:bg-gray-900 {{ empty($status) ? 'border-gray-900/25 shadow-xs dark:border-gray-600' : 'border-gray-200/90 hover:border-gray-300 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:border-gray-700 dark:hover:bg-gray-800/40' }}">
            <div class="flex w-full items-center justify-between gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pendaftar</span>
                <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
            </div>
            <div class="flex w-full items-end justify-between gap-2">
                <span class="text-2xl font-bold leading-none tracking-tight text-gray-900 dark:text-white tabular-nums">{{ $stats['total'] }}</span>
                @if(empty($status))
                    <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <span class="size-1.5 rounded-full bg-blue-500"></span>
                        Semua
                    </span>
                @else
                    <span class="text-[11px] font-medium text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300">Tampilkan semua</span>
                @endif
            </div>
        </button>

        {{-- Card Menunggu (Pending) --}}
        <button type="button"
                wire:click="setStatusFilter('pending')"
                aria-pressed="{{ $status === 'pending' ? 'true' : 'false' }}"
                class="group flex h-[102px] w-full flex-col justify-between rounded-xl border px-4 py-3.5 text-left transition-colors cursor-pointer bg-white dark:bg-gray-900 {{ $status === 'pending' ? 'border-gray-900/25 shadow-xs dark:border-gray-600' : 'border-gray-200/90 hover:border-gray-300 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:border-gray-700 dark:hover:bg-gray-800/40' }}">
            <div class="flex w-full items-center justify-between gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Menunggu Verifikasi</span>
                <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-700 dark:bg-warning-400/15 dark:text-warning-300">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="flex w-full items-end justify-between gap-2">
                <span class="text-2xl font-bold leading-none tracking-tight text-gray-900 dark:text-white tabular-nums">{{ $stats['pending'] }}</span>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium {{ $status === 'pending' ? 'rounded-md bg-warning-50 px-2 py-0.5 font-semibold text-warning-800 dark:bg-warning-400/15 dark:text-warning-300' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="size-1.5 rounded-full bg-warning-500"></span>
                    {{ $status === 'pending' ? 'Filter Aktif' : 'Pending' }}
                </span>
            </div>
        </button>

        {{-- Card Diterima (Approved) --}}
        <button type="button"
                wire:click="setStatusFilter('accepted')"
                aria-pressed="{{ in_array($status, ['accepted', 'approved'], true) ? 'true' : 'false' }}"
                class="group flex h-[102px] w-full flex-col justify-between rounded-xl border px-4 py-3.5 text-left transition-colors cursor-pointer bg-white dark:bg-gray-900 {{ in_array($status, ['accepted', 'approved'], true) ? 'border-gray-900/25 shadow-xs dark:border-gray-600' : 'border-gray-200/90 hover:border-gray-300 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:border-gray-700 dark:hover:bg-gray-800/40' }}">
            <div class="flex w-full items-center justify-between gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Diterima (Approved)</span>
                <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="flex w-full items-end justify-between gap-2">
                <span class="text-2xl font-bold leading-none tracking-tight text-gray-900 dark:text-white tabular-nums">{{ $stats['accepted'] }}</span>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium {{ in_array($status, ['accepted', 'approved'], true) ? 'rounded-md bg-success-50 px-2 py-0.5 font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-300' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="size-1.5 rounded-full bg-success-500"></span>
                    {{ in_array($status, ['accepted', 'approved'], true) ? 'Filter Aktif' : 'Approved' }}
                </span>
            </div>
        </button>

        {{-- Card Ditolak (Rejected) --}}
        <button type="button"
                wire:click="setStatusFilter('rejected')"
                aria-pressed="{{ $status === 'rejected' ? 'true' : 'false' }}"
                class="group flex h-[102px] w-full flex-col justify-between rounded-xl border px-4 py-3.5 text-left transition-colors cursor-pointer bg-white dark:bg-gray-900 {{ $status === 'rejected' ? 'border-gray-900/25 shadow-xs dark:border-gray-600' : 'border-gray-200/90 hover:border-gray-300 hover:bg-gray-50/50 dark:border-gray-800 dark:hover:border-gray-700 dark:hover:bg-gray-800/40' }}">
            <div class="flex w-full items-center justify-between gap-2">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Ditolak (Rejected)</span>
                <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="flex w-full items-end justify-between gap-2">
                <span class="text-2xl font-bold leading-none tracking-tight text-gray-900 dark:text-white tabular-nums">{{ $stats['rejected'] }}</span>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-medium {{ $status === 'rejected' ? 'rounded-md bg-error-50 px-2 py-0.5 font-semibold text-error-700 dark:bg-error-500/15 dark:text-error-300' : 'text-gray-500 dark:text-gray-400' }}">
                    <span class="size-1.5 rounded-full bg-error-500"></span>
                    {{ $status === 'rejected' ? 'Filter Aktif' : 'Rejected' }}
                </span>
            </div>
        </button>
    </div>

    {{-- 2. Single-Row Search & Filter Toolbar (Tanpa container luar besar, tinggi konsisten 42px) --}}
    <div class="mb-4 flex flex-col lg:flex-row lg:items-center gap-2.5">
        {{-- Pencarian Realtime --}}
        <div class="relative flex-1 min-w-[220px]">
            <label for="ppdb-search-input" class="sr-only">Cari nama santri, nomor registrasi, atau nama wali</label>
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input id="ppdb-search-input"
                   type="search"
                   wire:model.live.debounce.250ms="search"
                   autocorrect="off" autocapitalize="off" spellcheck="false"
                   placeholder="Cari nama santri, no. registrasi, wali..."
                   class="h-[42px] w-full rounded-lg border border-gray-200 bg-white pl-9 pr-8 text-sm text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500">
            @if(!empty($search))
                <button type="button"
                        wire:click="$set('search', '')"
                        aria-label="Bersihkan pencarian"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 cursor-pointer">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 lg:flex lg:items-center gap-2.5">
            {{-- Filter Program --}}
            <div>
                <label for="ppdb-filter-program" class="sr-only">Filter Program Pendidikan</label>
                <select id="ppdb-filter-program"
                        wire:model.live="target_class"
                        class="h-[42px] w-full lg:w-48 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">Semua Program</option>
                    @foreach($classes as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <label for="ppdb-filter-status" class="sr-only">Filter Status</label>
                <select id="ppdb-filter-status"
                        wire:model.live="status"
                        class="h-[42px] w-full lg:w-44 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending (Menunggu)</option>
                    <option value="accepted">Approved (Diterima)</option>
                    <option value="rejected">Rejected (Ditolak)</option>
                </select>
            </div>

            {{-- Filter Tahun Ajaran --}}
            <div>
                <label for="ppdb-filter-year" class="sr-only">Filter Tahun Ajaran</label>
                <select id="ppdb-filter-year"
                        wire:model.live="academic_year"
                        class="h-[42px] w-full lg:w-44 rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    <option value="all">Semua Tahun Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year }}">
                            TA {{ $year }}{{ $year === $activeYear ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Reset Filter (Muncul hanya ketika ada filter aktif) --}}
            @if($hasActiveFilters)
                <button type="button"
                        wire:click="resetFilters"
                        class="inline-flex h-[42px] shrink-0 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition cursor-pointer">
                    <svg class="size-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reset Filter
                </button>
            @endif
        </div>
    </div>

    {{-- 3. Tabel Pendaftar Clean + Export di Kanan Atas Tabel --}}
    <div class="rounded-xl border border-gray-200/90 bg-white shadow-2xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
        {{-- Header Atas Tabel: Judul / Bulk Actions di kiri, Tombol Export di kanan atas --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 sm:px-5 py-3 dark:border-gray-800/80">
            <div class="flex flex-wrap items-center gap-2.5">
                @if(count($selectedIds) > 0)
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">
                            {{ count($selectedIds) }} dipilih
                        </span>
                        <button type="button"
                                @click="openBulkModal('accepted', 'Terima (Approve)')"
                                class="inline-flex h-8 items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                            Approve
                        </button>
                        <button type="button"
                                @click="openBulkModal('pending', 'Kembalikan ke Pending')"
                                class="inline-flex h-8 items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                            Set Pending
                        </button>
                        <button type="button"
                                @click="openBulkModal('rejected', 'Tolak (Reject)')"
                                class="inline-flex h-8 items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                            Reject
                        </button>
                        <button type="button"
                                @click="openBulkModal('delete', 'Hapus (Arsipkan)')"
                                class="inline-flex h-8 items-center gap-1 rounded-lg border border-error-200 bg-white px-2.5 text-xs font-semibold text-error-600 hover:bg-error-50 dark:border-error-800/70 dark:bg-gray-800 dark:text-error-400 dark:hover:bg-error-500/10 transition cursor-pointer">
                            Hapus
                        </button>
                        <button type="button"
                                wire:click="$set('selectedIds', []); $set('selectAll', false)"
                                class="text-xs font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 px-1.5 cursor-pointer">
                            Batal
                        </button>
                    </div>
                @else
                    <h2 id="ppdb-table-heading" class="text-sm font-semibold text-gray-900 dark:text-gray-100">Data Pendaftar</h2>
                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400 tabular-nums">
                        {{ $registrations->total() }} pendaftar
                    </span>
                @endif

                <div wire:loading.delay wire:target="search, target_class, status, academic_year, sortBy, perPage, resetFilters, setStatusFilter" class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <svg class="size-3.5 animate-spin text-primary-600 dark:text-primary-400" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"></path>
                    </svg>
                    <span>Memuat...</span>
                </div>
            </div>

            {{-- Tombol Export di Kanan Atas Tabel --}}
            <a href="{{ route('admin.registrations.index', ['action' => 'export', 'search' => $search, 'status' => $status, 'target_class' => $target_class, 'academic_year' => $academic_year]) }}"
               class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition shadow-2xs">
                <svg class="size-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
        </div>

        {{-- Accessible Horizontal Scroll Region untuk layar kecil --}}
        <div tabindex="0" role="region" aria-labelledby="ppdb-table-heading" class="overflow-x-auto focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-500">
            <table class="min-w-[760px] w-full border-collapse text-left">
                <thead class="border-b border-gray-100 bg-white dark:border-gray-800/80 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="w-11 pl-4 sm:pl-5 pr-2 py-3">
                            <input type="checkbox"
                                   wire:model.live="selectAll"
                                   aria-label="Pilih semua pendaftar"
                                   class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900">
                        </th>

                        {{-- Sortable: Nomor Registrasi --}}
                        <th scope="col"
                            aria-sort="{{ $sortField === 'registration_number' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}"
                            class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            <button type="button"
                                    wire:click="sortBy('registration_number')"
                                    class="inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-gray-200 transition cursor-pointer">
                                <span>No. Registrasi</span>
                                <span class="inline-flex flex-col text-gray-400" aria-hidden="true">
                                    @if($sortField === 'registration_number')
                                        @if($sortDirection === 'asc')
                                            <svg class="size-3.5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg class="size-3.5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg class="size-3.5 opacity-50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    @endif
                                </span>
                            </button>
                        </th>

                        {{-- Sortable: Nama Santri --}}
                        <th scope="col"
                            aria-sort="{{ $sortField === 'student_name' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}"
                            class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            <button type="button"
                                    wire:click="sortBy('student_name')"
                                    class="inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-gray-200 transition cursor-pointer">
                                <span>Nama Santri &amp; Program</span>
                                <span class="inline-flex flex-col text-gray-400" aria-hidden="true">
                                    @if($sortField === 'student_name')
                                        @if($sortDirection === 'asc')
                                            <svg class="size-3.5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg class="size-3.5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg class="size-3.5 opacity-50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    @endif
                                </span>
                            </button>
                        </th>

                        {{-- Sortable: Tanggal --}}
                        <th scope="col"
                            aria-sort="{{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}"
                            class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            <button type="button"
                                    wire:click="sortBy('created_at')"
                                    class="inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-gray-200 transition cursor-pointer">
                                <span>Tanggal Daftar</span>
                                <span class="inline-flex flex-col text-gray-400" aria-hidden="true">
                                    @if($sortField === 'created_at')
                                        @if($sortDirection === 'asc')
                                            <svg class="size-3.5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg class="size-3.5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg class="size-3.5 opacity-50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    @endif
                                </span>
                            </button>
                        </th>

                        {{-- Sortable: Status --}}
                        <th scope="col"
                            aria-sort="{{ $sortField === 'status' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}"
                            class="px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            <button type="button"
                                    wire:click="sortBy('status')"
                                    class="inline-flex items-center gap-1 hover:text-gray-900 dark:hover:text-gray-200 transition cursor-pointer">
                                <span>Status</span>
                                <span class="inline-flex flex-col text-gray-400" aria-hidden="true">
                                    @if($sortField === 'status')
                                        @if($sortDirection === 'asc')
                                            <svg class="size-3.5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                        @else
                                            <svg class="size-3.5 text-gray-800 dark:text-gray-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        @endif
                                    @else
                                        <svg class="size-3.5 opacity-50" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                                    @endif
                                </span>
                            </button>
                        </th>

                        <th scope="col" class="w-16 pl-2 pr-4 sm:pr-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800/80 dark:bg-gray-900">
                    @forelse($registrations as $item)
                        <tr wire:key="reg-row-{{ $item->id }}" class="h-16 bg-white hover:bg-gray-50/75 dark:bg-gray-900 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="pl-4 sm:pl-5 pr-2 py-2.5 whitespace-nowrap">
                                <input type="checkbox"
                                       wire:model.live="selectedIds"
                                       value="{{ (string) $item->id }}"
                                       aria-label="Pilih {{ $item->student_name }}"
                                       class="size-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900">
                            </td>

                            <td class="px-4 py-2.5 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 font-mono">
                                {{ $item->registration_number }}
                            </td>

                            <td class="px-4 py-2.5">
                                <a href="{{ route('admin.registrations.show', $item->id) }}" class="text-sm font-semibold text-gray-900 hover:text-primary-700 dark:text-gray-100 dark:hover:text-primary-400 transition-colors line-clamp-1">
                                    {{ $item->student_name }}
                                </a>
                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                    {{ $item->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} · {{ $item->target_class }} · TA {{ $item->academic_year }}
                                </div>
                            </td>

                            <td class="px-4 py-2.5 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-400 tabular-nums">
                                {{ $item->created_at->format('d M Y') }}
                            </td>

                            <td class="px-4 py-2.5 whitespace-nowrap">
                                @if($item->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-800 ring-1 ring-inset ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-300 dark:ring-warning-400/25">
                                        <span class="size-1.5 rounded-full bg-warning-500"></span>
                                        Pending
                                    </span>
                                @elseif(in_array($item->status, ['accepted', 'approved'], true))
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/20 dark:bg-success-500/10 dark:text-success-300 dark:ring-success-500/25">
                                        <span class="size-1.5 rounded-full bg-success-500"></span>
                                        Approved
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-medium text-error-700 ring-1 ring-inset ring-error-600/20 dark:bg-error-500/10 dark:text-error-300 dark:ring-error-500/25">
                                        <span class="size-1.5 rounded-full bg-error-500"></span>
                                        Rejected
                                    </span>
                                @endif
                            </td>

                            {{-- 4. Dropdown Aksi Tiga Titik (...) --}}
                            <td class="pl-2 pr-4 sm:pr-5 py-2.5 whitespace-nowrap text-right">
                                <button type="button"
                                        id="reg-trigger-{{ $item->id }}"
                                        popovertarget="reg-panel-{{ $item->id }}"
                                        aria-label="Aksi untuk {{ $item->student_name }}"
                                        class="inline-flex size-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100 transition cursor-pointer">
                                    <svg class="size-[18px]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    </svg>
                                </button>

                                {{-- Viewport-Aware Button Group Popover (Top Layer, tidak terpotong overflow tabel) --}}
                                <div id="reg-panel-{{ $item->id }}"
                                     popover="auto"
                                     @toggle="positionFallbackPopover($event, 'reg-trigger-{{ $item->id }}')"
                                     class="ppdb-action-panel rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900 text-left">
                                    <a href="{{ route('admin.registrations.show', $item->id) }}"
                                       class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 transition">
                                        <svg class="size-4 shrink-0 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Lihat Detail
                                    </a>

                                    <a href="{{ route('admin.registrations.edit', $item->id) }}"
                                       class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 transition">
                                        <svg class="size-4 shrink-0 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                        Edit Data
                                    </a>

                                    <button type="button"
                                            @click="$el.closest('[popover]')?.hidePopover(); openStatusModal({{ $item->id }}, @js($item->student_name), @js($item->registration_number), @js($item->status))"
                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 transition cursor-pointer">
                                        <svg class="size-4 shrink-0 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                        Ubah Status
                                    </button>

                                    <hr class="my-1 border-gray-100 dark:border-gray-800">

                                    {{-- Warna merah hanya untuk tindakan Hapus --}}
                                    <button type="button"
                                            @click="$el.closest('[popover]')?.hidePopover(); openDeleteModal({{ $item->id }}, @js($item->student_name), @js($item->registration_number))"
                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 transition cursor-pointer">
                                        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada data pendaftar yang cocok</p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Coba sesuaikan kata kunci pencarian atau filter status di atas.</p>
                                @if($hasActiveFilters)
                                    <button type="button"
                                            wire:click="resetFilters"
                                            class="mt-3 inline-flex h-9 items-center rounded-lg border border-gray-200 bg-white px-3.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 cursor-pointer">
                                        Reset Filter
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Tabel: Jumlah Hasil, Pilihan Baris per Halaman, & Pagination --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-gray-100 bg-white px-4 sm:px-5 py-3 dark:border-gray-800/80 dark:bg-gray-900">
            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                <span>
                    Menampilkan
                    <strong class="font-semibold text-gray-800 dark:text-gray-200 tabular-nums">{{ $registrations->firstItem() ?? 0 }}–{{ $registrations->lastItem() ?? 0 }}</strong>
                    dari
                    <strong class="font-semibold text-gray-800 dark:text-gray-200 tabular-nums">{{ $registrations->total() }}</strong>
                    hasil
                </span>

                <div class="inline-flex items-center gap-1.5">
                    <label for="ppdb-per-page" class="text-xs text-gray-500 dark:text-gray-400">Baris:</label>
                    <select id="ppdb-per-page"
                            wire:model.live="perPage"
                            class="h-8 rounded-lg border border-gray-200 bg-white px-2 text-xs font-medium text-gray-700 focus:border-primary-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div>
                @if($registrations->hasPages())
                    {{ $registrations->links() }}
                @else
                    <nav aria-label="Pagination" class="inline-flex items-center gap-1">
                        <span class="inline-flex h-8 items-center rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs font-medium text-gray-400 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-500">Sebelumnya</span>
                        <span aria-current="page" class="inline-flex size-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">1</span>
                        <span class="inline-flex h-8 items-center rounded-lg border border-gray-200 bg-gray-50 px-2.5 text-xs font-medium text-gray-400 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-500">Berikutnya</span>
                    </nav>
                @endif
            </div>
        </div>
    </div>

    {{-- Dialog Modal: Ubah Status Pendaftaran --}}
    <dialog x-ref="statusDialog"
            wire:ignore.self
            id="ppdb-status-dialog"
            aria-labelledby="ppdb-status-dialog-title"
            class="m-auto w-full max-w-md rounded-2xl border border-gray-200 bg-white p-0 text-left shadow-xl backdrop:bg-gray-950/50 dark:border-gray-800 dark:bg-gray-900">
        <div class="p-6">
            <h2 id="ppdb-status-dialog-title" class="text-base font-bold text-gray-900 dark:text-white">Ubah Status Pendaftaran</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Pendaftar: <strong class="font-semibold text-gray-800 dark:text-gray-200" x-text="statusTarget.name"></strong>
                (<span class="font-mono" x-text="statusTarget.regNo"></span>)
            </p>

            <div class="mt-4 space-y-2">
                <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition"
                       :class="newStatus === 'pending' ? 'border-primary-500 bg-primary-50/50 dark:border-primary-500/60 dark:bg-primary-500/10' : 'border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/60'">
                    <input type="radio" name="modal_status_choice" value="pending" x-model="newStatus" class="text-primary-600 focus:ring-primary-500">
                    <div>
                        <div class="text-xs font-semibold text-gray-900 dark:text-gray-100">Pending (Menunggu Verifikasi)</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Data masih dalam tahap peninjauan panitia.</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition"
                       :class="newStatus === 'accepted' ? 'border-primary-500 bg-primary-50/50 dark:border-primary-500/60 dark:bg-primary-500/10' : 'border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/60'">
                    <input type="radio" name="modal_status_choice" value="accepted" x-model="newStatus" class="text-primary-600 focus:ring-primary-500">
                    <div>
                        <div class="text-xs font-semibold text-gray-900 dark:text-gray-100">Approved (Diterima)</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Menerima calon santri &amp; menerbitkan NIS/akun SIAKAD otomatis.</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition"
                       :class="newStatus === 'rejected' ? 'border-primary-500 bg-primary-50/50 dark:border-primary-500/60 dark:bg-primary-500/10' : 'border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/60'">
                    <input type="radio" name="modal_status_choice" value="rejected" x-model="newStatus" class="text-primary-600 focus:ring-primary-500">
                    <div>
                        <div class="text-xs font-semibold text-gray-900 dark:text-gray-100">Rejected (Ditolak)</div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Menolak berkas pendaftaran tanpa menghapus riwayat data.</div>
                    </div>
                </label>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2.5">
                <form method="dialog">
                    <button type="submit" value="cancel" class="h-10 rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                        Batal
                    </button>
                </form>
                <button type="button"
                        @click="$wire.updateSingleStatus(statusTarget.id, newStatus); $refs.statusDialog.close()"
                        class="h-10 rounded-lg bg-primary-600 hover:bg-primary-700 px-4 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                    Simpan Status
                </button>
            </div>
        </div>
    </dialog>

    {{-- Dialog Modal Konfirmasi Hapus (Wajib sebelum menghapus data, mendukung Soft Delete & Audit Log) --}}
    <dialog x-ref="deleteDialog"
            wire:ignore.self
            id="ppdb-delete-dialog"
            aria-labelledby="ppdb-delete-dialog-title"
            class="m-auto w-full max-w-md rounded-2xl border border-gray-200 bg-white p-0 text-left shadow-xl backdrop:bg-gray-950/50 dark:border-gray-800 dark:bg-gray-900">
        <div class="p-6">
            <div class="flex items-start gap-3.5">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 id="ppdb-delete-dialog-title" class="text-base font-bold text-gray-900 dark:text-white">Hapus data pendaftar?</h2>
                    <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-400">
                        Anda akan menghapus data pendaftar <strong class="font-semibold text-gray-900 dark:text-gray-200" x-text="deleteTarget.name"></strong> (<span class="font-mono text-xs" x-text="deleteTarget.regNo"></span>). Data akan diarsipkan melalui <em>soft delete</em> dan dicatat pada Log Aktivitas.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2.5">
                <form method="dialog">
                    <button type="submit" value="cancel" autofocus class="h-10 rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                        Batal
                    </button>
                </form>
                <button type="button"
                        @click="$wire.deleteRegistration(deleteTarget.id); $refs.deleteDialog.close()"
                        class="h-10 rounded-lg bg-error-600 hover:bg-error-700 px-4 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                    Ya, Hapus Data
                </button>
            </div>
        </div>
    </dialog>

    {{-- Dialog Modal Konfirmasi Aksi Massal --}}
    <dialog x-ref="bulkDialog"
            wire:ignore.self
            id="ppdb-bulk-dialog"
            aria-labelledby="ppdb-bulk-dialog-title"
            class="m-auto w-full max-w-md rounded-2xl border border-gray-200 bg-white p-0 text-left shadow-xl backdrop:bg-gray-950/50 dark:border-gray-800 dark:bg-gray-900">
        <div class="p-6">
            <h2 id="ppdb-bulk-dialog-title" class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi Aksi Massal</h2>
            <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-400">
                Terapkan tindakan <strong class="font-semibold text-gray-900 dark:text-gray-200" x-text="bulkTargetLabel"></strong> pada <strong class="font-semibold text-gray-900 dark:text-gray-200">{{ count($selectedIds) }}</strong> data pendaftar terpilih?
            </p>
            <div class="mt-6 flex items-center justify-end gap-2.5">
                <form method="dialog">
                    <button type="submit" value="cancel" autofocus class="h-10 rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition cursor-pointer">
                        Batal
                    </button>
                </form>
                <button type="button"
                        @click="$wire.set('bulkAction', bulkTargetAction); $wire.executeBulkStatus(); $refs.bulkDialog.close()"
                        :class="bulkTargetAction === 'delete' ? 'bg-error-600 hover:bg-error-700' : 'bg-primary-600 hover:bg-primary-700'"
                        class="h-10 rounded-lg px-4 text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </dialog>

    @once
        <script type="module">
            if (!("popover" in HTMLElement.prototype)) {
                import("https://unpkg.com/@oddbird/popover-polyfill@latest/dist/popover.min.js");
            }
        </script>
    @endonce
</div>
