<div>
    {{-- Statistics Cards (Klik untuk filter instan berdasarkan status) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Card Total --}}
        <button type="button" 
                wire:click="setStatusFilter('')"
                class="text-left group bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden transition-all duration-200 hover:scale-[1.02] hover:shadow-md {{ empty($status) ? 'ring-4 ring-offset-2 ring-blue-500' : 'opacity-90 hover:opacity-100' }}">
            <div class="absolute right-4 bottom-4 text-white opacity-20 group-hover:scale-110 transition-transform">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider opacity-90">Total Pendaftar</p>
                @if(empty($status))
                    <span class="text-[10px] bg-white/20 font-bold px-2 py-0.5 rounded-full">Aktif</span>
                @endif
            </div>
            <h3 class="text-3xl font-extrabold mt-1">{{ $stats['total'] }}</h3>
            <p class="text-[11px] mt-2 text-blue-100 group-hover:underline">Klik tampilkan semua &rarr;</p>
        </button>
        
        {{-- Card Pending --}}
        <button type="button" 
                wire:click="setStatusFilter('pending')"
                class="text-left group bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden transition-all duration-200 hover:scale-[1.02] hover:shadow-md {{ $status === 'pending' ? 'ring-4 ring-offset-2 ring-amber-500' : 'opacity-90 hover:opacity-100' }}">
            <div class="absolute right-4 bottom-4 text-white opacity-20 group-hover:scale-110 transition-transform">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 11h-2V7h2v6zm0 4h-2v-2h2v2z"/></svg>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider opacity-90">Menunggu (Pending)</p>
                @if($status === 'pending')
                    <span class="text-[10px] bg-white/20 font-bold px-2 py-0.5 rounded-full">Aktif</span>
                @endif
            </div>
            <h3 class="text-3xl font-extrabold mt-1">{{ $stats['pending'] }}</h3>
            <p class="text-[11px] mt-2 text-amber-100 group-hover:underline">Klik filter pending &rarr;</p>
        </button>

        {{-- Card Approved --}}
        <button type="button" 
                wire:click="setStatusFilter('accepted')"
                class="text-left group bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden transition-all duration-200 hover:scale-[1.02] hover:shadow-md {{ in_array($status, ['accepted', 'approved'], true) ? 'ring-4 ring-offset-2 ring-emerald-500' : 'opacity-90 hover:opacity-100' }}">
            <div class="absolute right-4 bottom-4 text-white opacity-20 group-hover:scale-110 transition-transform">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider opacity-90">Diterima (Approved)</p>
                @if(in_array($status, ['accepted', 'approved'], true))
                    <span class="text-[10px] bg-white/20 font-bold px-2 py-0.5 rounded-full">Aktif</span>
                @endif
            </div>
            <h3 class="text-3xl font-extrabold mt-1">{{ $stats['accepted'] }}</h3>
            <p class="text-[11px] mt-2 text-emerald-100 group-hover:underline">Klik filter diterima &rarr;</p>
        </button>

        {{-- Card Rejected --}}
        <button type="button" 
                wire:click="setStatusFilter('rejected')"
                class="text-left group bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden transition-all duration-200 hover:scale-[1.02] hover:shadow-md {{ $status === 'rejected' ? 'ring-4 ring-offset-2 ring-rose-500' : 'opacity-90 hover:opacity-100' }}">
            <div class="absolute right-4 bottom-4 text-white opacity-20 group-hover:scale-110 transition-transform">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider opacity-90">Ditolak (Rejected)</p>
                @if($status === 'rejected')
                    <span class="text-[10px] bg-white/20 font-bold px-2 py-0.5 rounded-full">Aktif</span>
                @endif
            </div>
            <h3 class="text-3xl font-extrabold mt-1">{{ $stats['rejected'] }}</h3>
            <p class="text-[11px] mt-2 text-rose-100 group-hover:underline">Klik filter ditolak &rarr;</p>
        </button>
    </div>

    {{-- Search & Filter Panel Realtime Livewire --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5 mb-6">
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                
                {{-- Input Pencarian Realtime --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Pencarian Realtime
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" 
                               wire:model.live.debounce.300ms="search"
                               autocorrect="off" autocapitalize="off" spellcheck="false"
                               placeholder="Ketik nama, no. registrasi, wali..." 
                               class="w-full min-h-11 rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm pl-10 pr-9 py-2">
                        @if(!empty($search))
                            <button type="button" 
                                    wire:click="$set('search', '')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs font-bold"
                                    title="Hapus pencarian">
                                &times;
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Filter Program Pendidikan --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Program Pendidikan</label>
                    <select wire:model.live="target_class" 
                            class="w-full min-h-11 rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
                        <option value="">-- Semua Program --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Status</label>
                    <select wire:model.live="status" 
                            class="w-full min-h-11 rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
                        <option value="">-- Semua Status --</option>
                        <option value="pending">Pending (Menunggu)</option>
                        <option value="accepted">Approved (Diterima)</option>
                        <option value="rejected">Rejected (Ditolak)</option>
                    </select>
                </div>

                {{-- Filter Tahun Ajaran --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tahun Ajaran</label>
                    <select wire:model.live="academic_year" 
                            class="w-full min-h-11 rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
                        <option value="all">-- Semua Tahun --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}">
                                {{ $year }} @if($year === $activeYear) (Aktif) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- Baris Status & Loading & Export --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    @if(!empty($search) || !empty($target_class) || !empty($status) || $academic_year !== $activeYear)
                        <button type="button" 
                                wire:click="resetFilters" 
                                class="min-h-11 inline-flex items-center px-4 py-2 border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:slate-800 transition text-sm">
                            Clear Filter
                        </button>
                    @endif

                    <div wire:loading.delay wire:target="search, target_class, status, academic_year" class="inline-flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                        <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>Menyaring realtime...</span>
                    </div>
                </div>

                <a href="{{ route('admin.registrations.index', ['action' => 'export', 'search' => $search, 'status' => $status, 'target_class' => $target_class, 'academic_year' => $academic_year]) }}" 
                   class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm font-semibold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh Data Excel (.csv)
                </a>
            </div>
        </div>
    </div>

    {{-- Panel Aksi Massal (Bulk Actions) --}}
    @if(count($selectedIds) > 0)
    <div class="mb-6 p-4 bg-green-50 border border-green-200 dark:bg-slate-800 dark:border-slate-700 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 animate-fade-in">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-green-100 dark:bg-slate-700 text-green-700 dark:text-green-300 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <span class="text-sm font-bold text-gray-800 dark:text-slate-100">
                    Aksi Massal: <span class="text-green-700 dark:text-green-400 font-extrabold">{{ count($selectedIds) }}</span> Pendaftar Terpilih
                </span>
                <p class="text-xs text-gray-500 dark:text-slate-400">Pilih status baru untuk semua pendaftar yang dicentang.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" 
                    wire:click="$set('bulkAction', 'accepted'); executeBulkStatus()" 
                    onclick="return confirm('Approve/terima semua calon santri yang dipilih?')" 
                    class="px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 9L19 7"/></svg>
                Approve Terpilih
            </button>
            <button type="button" 
                    wire:click="$set('bulkAction', 'rejected'); executeBulkStatus()" 
                    onclick="return confirm('Tolak semua pendaftar terpilih?')" 
                    class="px-4 py-2 bg-red-600 hover:bg-red-750 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Reject Terpilih
            </button>
            <button type="button" 
                    wire:click="$set('bulkAction', 'pending'); executeBulkStatus()" 
                    onclick="return confirm('Kembalikan status pendaftar terpilih menjadi Pending?')" 
                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Set Pending
            </button>
            <button type="button" 
                    wire:click="$set('selectedIds', []); $set('selectAll', false)" 
                    class="px-3 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg text-xs hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                Batal
            </button>
        </div>
    </div>
    @endif

    {{-- Tabel Pendaftar PPDB --}}
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 dark:border-slate-700 text-green-700 focus:ring-green-500 w-4 h-4">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">No. Registrasi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama Santri &amp; Pilihan Program</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Tanggal Daftar</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-950 divide-y divide-gray-200 dark:divide-slate-700">
                    @forelse($registrations as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/40 transition">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ (string) $item->id }}" class="rounded border-gray-300 dark:border-slate-700 text-green-700 focus:ring-green-500 w-4 h-4">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-slate-100">
                            {{ $item->registration_number }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-slate-100">{{ $item->student_name }}</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400">
                                JK: {{ $item->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} | {{ $item->target_class }} | TA: {{ $item->academic_year }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $item->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->status == 'pending')
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    Pending
                                </span>
                            @elseif($item->status == 'accepted')
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200">
                                    Approved
                                </span>
                            @else
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 border border-red-200">
                                    Rejected
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.registrations.show', $item->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg transition font-semibold">Detail</a>
                            <form action="{{ route('admin.registrations.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pendaftar {{ $item->student_name }} secara permanen?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 px-3 py-1.5 bg-red-50 dark:bg-red-500/10 rounded-lg transition font-semibold">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-slate-400">
                            Belum ada data pendaftar yang cocok dengan filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($registrations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
            {{ $registrations->links() }}
        </div>
        @endif
    </div>
</div>
