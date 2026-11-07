@extends('layouts.admin')

@section('title', 'Manajemen Fasilitas')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Fasilitas</h1>
        <p class="text-gray-600">Kelola data fasilitas Pondok Pesantren.</p>
    </div>
    <a href="{{ route('admin.facilities.create') }}" class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition shadow-sm font-medium">
        + Tambah Fasilitas
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
    {{ session('success') }}
</div>
@endif

<div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table-builder min-w-full divide-y divide-gray-200 dark:divide-slate-700">
            <thead>
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 dark:text-slate-400">Gambar/Ikon</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600">Nama Fasilitas</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-950 divide-y divide-gray-200 dark:divide-slate-700">
                @forelse($facilities as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item->icon_or_image_path)
                            <img src="{{ Storage::url($item->icon_or_image_path) }}" alt="{{ $item->title }}" class="h-12 w-16 object-cover rounded">
                        @else
                            <div class="h-12 w-16 bg-gray-200 dark:bg-slate-700 flex items-center justify-center rounded text-gray-400 dark:text-slate-400 text-xs">No Image</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-slate-100">{{ $item->title }}</div>
                        <div class="text-sm text-gray-600 dark:text-slate-400 line-clamp-1">{{ Str::limit($item->description, 50) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <x-icon-button type="edit" href="{{ route('admin.facilities.edit', $item->id) }}" class="mr-1.5" />
                        <form action="{{ route('admin.facilities.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus fasilitas ini?');">
                            @csrf
                            @method('DELETE')
                            <x-icon-button type="delete" :submit="true" />
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-10 text-center text-gray-600 dark:text-slate-400">
                        Belum ada data fasilitas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($facilities->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
        {{ $facilities->links() }}
    </div>
    @endif
</div>
@endsection
