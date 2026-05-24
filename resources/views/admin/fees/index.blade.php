@extends('layouts.admin')

@section('title', 'Biaya Pendidikan')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Daftar Biaya Pendidikan</h1>
        <p class="text-gray-600 text-sm">Kelola daftar komponen biaya pendidikan santri berdasarkan jenjang.</p>
    </div>
    <a href="{{ route('admin.fees.create') }}" class="bg-[#0D6B3A] text-white px-4 py-2 rounded-md hover:bg-green-800 transition shadow flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Komponen Biaya
    </a>
</div>

@if (session('success'))
    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@php
    $categories = ['MTs' => 'Madrasah Tsanawiyah (MTs)', 'SMK Otomotif' => 'SMK Teknik Otomotif', 'MA' => 'Madrasah Aliyah (MA)', 'SMK DKV' => 'SMK Desain Komunikasi Visual (DKV)'];
@endphp

<div class="space-y-8">
    @foreach($categories as $key => $title)
        @php $categoryFees = $fees->where('category', $key); @endphp
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-slate-700">
            <div class="bg-emerald-800 text-white px-6 py-4 flex justify-between items-center">
                <h2 class="font-bold text-md tracking-wide">{{ $title }}</h2>
                <span class="bg-emerald-700 px-3 py-1 rounded-full text-xs font-semibold">{{ $categoryFees->count() }} Komponen</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-slate-800 text-gray-700 dark:text-slate-300 uppercase font-semibold text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Nama Komponen</th>
                            <th class="px-6 py-3">Nominal (Rupiah)</th>
                            <th class="px-6 py-3">Keterangan</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700 text-gray-700 dark:text-slate-300">
                        @forelse($categoryFees as $fee)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-slate-100">{{ $fee->name }}</td>
                                <td class="px-6 py-4">Rp {{ number_format($fee->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-gray-500 dark:text-slate-400">{{ $fee->description ?? '-' }}</td>
                                <td class="px-6 py-4 text-right flex justify-end gap-3">
                                    <a href="{{ route('admin.fees.edit', $fee->id) }}" class="text-blue-600 hover:text-blue-900 font-medium">Edit</a>
                                    <form action="{{ route('admin.fees.destroy', $fee->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komponen biaya ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400 dark:text-slate-400">Belum ada komponen biaya untuk jenjang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
