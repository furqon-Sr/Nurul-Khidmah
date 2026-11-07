@extends('layouts.admin')

@section('title', 'Manajemen Berita')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Berita</h1>
        <p class="text-gray-600">Kelola artikel dan berita untuk website.</p>
    </div>
    <a href="{{ route('admin.news.create') }}" class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition shadow-sm font-medium">
        + Tambah Berita
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 dark:text-slate-400">Gambar</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600">Judul & Kategori</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600">Tanggal Publikasi</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-950 divide-y divide-gray-200 dark:divide-slate-700">
                @forelse($news as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item->main_image_path)
                            <img src="{{ Storage::url($item->main_image_path) }}" alt="{{ $item->title }}" class="h-12 w-16 object-cover rounded">
                        @else
                            <div class="h-12 w-16 bg-gray-200 dark:bg-slate-700 flex items-center justify-center rounded text-gray-400 dark:text-slate-400 text-xs">No Image</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-slate-100">{{ $item->title }}</div>
                        <div class="text-sm text-gray-600 dark:text-slate-400">{{ $item->category ?? 'Tanpa Kategori' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $item->published_at ? $item->published_at->format('d M Y') : 'Draft' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <x-icon-button type="edit" href="{{ route('admin.news.edit', $item->id) }}" class="mr-1.5" />
                        <form action="{{ route('admin.news.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berita ini?');">
                            @csrf
                            @method('DELETE')
                            <x-icon-button type="delete" :submit="true" />
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-gray-600 dark:text-slate-400">
                        Belum ada data berita.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($news->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
        {{ $news->links() }}
    </div>
    @endif
</div>
@endsection
