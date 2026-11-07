@extends('layouts.admin')

@section('title', 'Manajemen Tahun Ajaran')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Manajemen Tahun Ajaran</h1>
        <p class="text-gray-600 dark:text-gray-400 text-sm">Kelola periode akademik dan tentukan semester aktif.</p>
    </div>
    <a href="{{ route('admin.academic-years.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Tahun Ajaran
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-emerald-100 dark:bg-emerald-950/50 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded shadow-sm text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-rose-100 dark:bg-rose-950/50 border-l-4 border-rose-500 text-rose-700 dark:text-rose-300 rounded shadow-sm text-sm">
    {{ session('error') }}
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table-builder min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Tahun Ajaran</th>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Semester</th>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Periode Tanggal</th>
                    <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-600 dark:text-gray-400">Status Aktif</th>
                    <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-600 dark:text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($tahunAjarans as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $item->nama_tahun_ajaran }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2.5 py-1 text-2xs uppercase tracking-wide font-bold rounded-md {{ $item->semester === 'Ganjil' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' }}">
                            {{ $item->semester }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                        @if($item->tanggal_mulai && $item->tanggal_selesai)
                            {{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_selesai->format('d M Y') }}
                        @else
                            <span class="text-gray-400 font-italic">Belum diatur</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($item->status_aktif)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-2xs uppercase tracking-wide font-bold rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                AKTIF
                            </span>
                        @else
                            <form action="{{ route('admin.academic-years.toggle', $item->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded bg-gray-100 hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 transition">
                                    Set Aktif
                                </button>
                            </form>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <x-icon-button type="edit" href="{{ route('admin.academic-years.edit', $item->id) }}" class="mr-1.5" />
                        @if(!$item->status_aktif)
                        <form action="{{ route('admin.academic-years.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini?');">
                            @csrf
                            @method('DELETE')
                            <x-icon-button type="delete" :submit="true" />
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada data Tahun Ajaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tahunAjarans->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        {{ $tahunAjarans->links() }}
    </div>
    @endif
</div>
@endsection
