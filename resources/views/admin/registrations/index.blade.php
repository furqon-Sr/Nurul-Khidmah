@extends('layouts.admin')
 
@section('title', 'Data Pendaftar')
 
@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Daftar Santri Baru</h1>
        <p class="text-gray-600">Kelola dan tinjau data pendaftaran santri baru.</p>
    </div>
</div>
 
@if(session('success'))
<div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
    {{ session('success') }}
</div>
@endif

<!-- Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute right-4 bottom-4 text-white opacity-20">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        </div>
        <p class="text-xs font-semibold uppercase tracking-wider opacity-75">Total Pendaftar</p>
        <h3 class="text-3xl font-extrabold mt-1">{{ $stats['total'] }}</h3>
    </div>
    
    <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute right-4 bottom-4 text-white opacity-20">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 11h-2V7h2v6zm0 4h-2v-2h2v2z"/></svg>
        </div>
        <p class="text-xs font-semibold uppercase tracking-wider opacity-75">Menunggu (Pending)</p>
        <h3 class="text-3xl font-extrabold mt-1">{{ $stats['pending'] }}</h3>
    </div>

    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute right-4 bottom-4 text-white opacity-20">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
        </div>
        <p class="text-xs font-semibold uppercase tracking-wider opacity-75">Diterima (Approved)</p>
        <h3 class="text-3xl font-extrabold mt-1">{{ $stats['accepted'] }}</h3>
    </div>

    <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute right-4 bottom-4 text-white opacity-20">
            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </div>
        <p class="text-xs font-semibold uppercase tracking-wider opacity-75">Ditolak (Rejected)</p>
        <h3 class="text-3xl font-extrabold mt-1">{{ $stats['rejected'] }}</h3>
    </div>
</div>

<!-- Search & Filter Form -->
<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-5 mb-6">
    <form action="{{ route('admin.registrations.index') }}" method="GET" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            
            <!-- Search Input -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Pencarian</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                           placeholder="Cari nama, no. registrasi, wali..." 
                           class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm pl-10 py-2">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Filter Program Pendidikan -->
            <div>
                <label for="target_class" class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Program Pendidikan</label>
                <select name="target_class" id="target_class" class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
                    <option value="">-- Semua Program --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class }}" {{ request('target_class') == $class ? 'selected' : '' }}>{{ $class }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Cabang -->
            <div>
                <label for="branch" class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Cabang</label>
                <select name="branch" id="branch" class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b }}" {{ request('branch') == $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label for="status" class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" id="status" class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
                    <option value="">-- Semua Status --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <!-- Filter Tahun Ajaran -->
            <div>
                <label for="academic_year" class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tahun Ajaran</label>
                <select name="academic_year" id="academic_year" class="w-full rounded-lg border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100 text-sm py-2">
                    <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>-- Semua Tahun --</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>
                            {{ $year }} @if($year === $activeYear) (Aktif) @endif
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <button type="submit" class="px-5 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg transition shadow-sm font-semibold text-sm">
                    Cari & Filter
                </button>
                @if(request()->anyFilled(['search', 'target_class', 'branch', 'status']) || $selectedYear !== $activeYear)
                    <a href="{{ route('admin.registrations.index') }}" class="px-4 py-2 border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition text-sm">
                        Clear Filter
                    </a>
                @endif
            </div>
            
            <button type="submit" name="action" value="export" class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh Data Excel (.csv)
            </button>
        </div>
    </form>
</div>

<form id="bulk-actions-form" action="{{ route('admin.registrations.bulk-status') }}" method="POST">
    @csrf
    
    <!-- Floating Bulk Actions Panel -->
    <div id="bulk-actions-panel" class="hidden mb-6 p-4 bg-green-50 border border-green-200 dark:bg-slate-800 dark:border-slate-700 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-green-100 dark:bg-slate-700 text-green-700 dark:text-green-300 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <span class="text-sm font-bold text-gray-800 dark:text-slate-100">
                    Aksi Massal: <span id="selected-count" class="text-green-700 dark:text-green-400">0</span> Pendaftar Terpilih
                </span>
                <p class="text-xs text-gray-500 dark:text-slate-400">Pilih status baru untuk semua pendaftar yang dicentang.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" name="status" value="accepted" onclick="return confirm('Approve/terima semua pendaftar terpilih?')" class="px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 9L19 7"/></svg>
                Approve Terpilih
            </button>
            <button type="submit" name="status" value="rejected" onclick="return confirm('Reject/tolak semua pendaftar terpilih?')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Reject Terpilih
            </button>
            <button type="submit" name="status" value="pending" onclick="return confirm('Kembalikan status pendaftar terpilih menjadi Pending?')" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Set Pending
            </button>
            <button type="button" onclick="deselectAll()" class="px-3 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 rounded-lg text-xs hover:bg-gray-100 dark:hover:bg-slate-700 transition">
                Batal
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left w-10">
                            <input type="checkbox" id="select-all-checkbox" class="rounded border-gray-300 dark:border-slate-700 text-green-700 focus:ring-green-500 w-4 h-4">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">No. Registrasi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama Santri & Pilihan Program</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Tanggal Daftar</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-950 divide-y divide-gray-200 dark:divide-slate-700">
                    @forelse($registrations as $item)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <input type="checkbox" name="registration_ids[]" value="{{ $item->id }}" class="registration-checkbox rounded border-gray-300 dark:border-slate-700 text-green-700 focus:ring-green-500 w-4 h-4">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-slate-100">
                            {{ $item->registration_number }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-slate-100">{{ $item->student_name }}</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400">
                                JK: {{ $item->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} | {{ $item->target_class }} (Cabang {{ $item->branch }}) | TA: {{ $item->academic_year }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.registrations.show', $item->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg transition font-semibold">Detail</a>
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
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const checkboxes = document.querySelectorAll('.registration-checkbox');
        const bulkPanel = document.getElementById('bulk-actions-panel');
        const selectedCountEl = document.getElementById('selected-count');

        function updateBulkPanel() {
            const checkedCount = document.querySelectorAll('.registration-checkbox:checked').length;
            selectedCountEl.textContent = checkedCount;
            if (checkedCount > 0) {
                bulkPanel.classList.remove('hidden');
            } else {
                bulkPanel.classList.add('hidden');
            }
            // Sync select all checkbox status
            if (checkboxes.length > 0) {
                selectAllCheckbox.checked = (checkedCount === checkboxes.length);
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAllCheckbox.checked;
                });
                updateBulkPanel();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkPanel);
        });

        window.deselectAll = function() {
            checkboxes.forEach(cb => {
                cb.checked = false;
            });
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            updateBulkPanel();
        }
    });
</script>
@endsection
