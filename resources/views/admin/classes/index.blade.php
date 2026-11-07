@extends('layouts.admin')

@section('title', 'Manajemen Data Kelas')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Manajemen Data Kelas</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">Kelola kelompok belajar, tingkat kelas, dan wali kelas.</p>
    </div>
    <a href="{{ route('admin.classes.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Kelas
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-emerald-100 dark:bg-emerald-950/50 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded shadow-sm text-sm">
    {{ session('success') }}
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
        <form method="GET" action="{{ route('admin.classes.index') }}" class="flex items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode atau Nama Kelas..."
                   class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-emerald-500 w-full max-w-xs">
            <button type="submit" class="px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-900">Cari</button>
            @if(request('search'))
                <a href="{{ route('admin.classes.index') }}" class="text-xs text-gray-600 hover:underline">Reset</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table-builder min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Kode / Kelas</th>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Tingkat</th>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Wali Kelas</th>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Tahun Ajaran</th>
                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-600 dark:text-gray-400">Jumlah Santri</th>
                    <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-600 dark:text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($classes as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150 class-row-item" data-search="{{ strtolower($item->kode_kelas . ' ' . $item->nama_kelas . ' ' . ($item->waliKelas->nama_lengkap ?? '')) }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item->nama_kelas }}</div>
                        <span class="font-mono text-xs text-gray-600">{{ $item->kode_kelas }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        {{ $item->tingkat_kelas }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 font-medium">
                        {{ $item->waliKelas->nama_lengkap ?? 'Belum Ditentukan' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        {{ $item->tahunAjaran->nama_tahun_ajaran ?? '-' }} ({{ $item->tahunAjaran->semester ?? '-' }})
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="px-2.5 py-1 text-2xs uppercase tracking-wide font-bold rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            {{ $item->santris_count ?? 0 }} Santri
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <x-icon-button type="edit" href="{{ route('admin.classes.edit', $item->id) }}" class="mr-1.5" />
                        <form action="{{ route('admin.classes.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                            @csrf
                            @method('DELETE')
                            <x-icon-button type="delete" :submit="true" />
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada data Kelas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($classes->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        {{ $classes->links() }}
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.querySelector('input[name="search"]');
        if (input) {
            let timer = null;
            input.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.class-row-item').forEach(r => {
                    const t = (r.getAttribute('data-search') || '').toLowerCase();
                    r.style.display = (!q || t.includes(q)) ? '' : 'none';
                });
                clearTimeout(timer);
                timer = setTimeout(() => {
                    input.closest('form')?.submit();
                }, 600);
            });
        }
    });
</script>
@endsection
