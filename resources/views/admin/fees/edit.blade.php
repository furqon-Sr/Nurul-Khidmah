@extends('layouts.admin')

@section('title', 'Edit Komponen Biaya')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.fees.index') }}" class="text-sm text-green-700 hover:underline flex items-center gap-1">
        &larr; Kembali ke Daftar Biaya
    </a>
    <h1 class="text-2xl font-bold text-gray-800 mt-2">Edit Komponen Biaya</h1>
    <p class="text-gray-600 text-sm">Ubah detail komponen biaya pendidikan yang sudah ada.</p>
</div>

@if ($errors->any())
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 max-w-2xl">
    <form action="{{ route('admin.fees.update', $fee->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-200 mb-2">Nama Komponen Biaya</label>
            <input type="text" name="name" value="{{ old('name', $fee->name) }}" required
                   class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-200 mb-2">Nominal (Rupiah)</label>
            <input type="number" name="amount" value="{{ old('amount', (int)$fee->amount) }}" required min="0"
                   class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-200 mb-2">Jenjang Pendidikan</label>
            <select name="category" required
                    class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
                <option value="" disabled>-- Pilih Program Pendidikan --</option>
                <!-- Include legacy options just in case there's old data not matching new program names -->
                @if(!in_array($fee->category, $programs->pluck('name')->toArray()) && $fee->category)
                    <option value="{{ $fee->category }}" selected>{{ $fee->category }} (Legacy)</option>
                @endif
                @foreach($programs as $program)
                    <option value="{{ $program->name }}" {{ old('category', $fee->category) == $program->name ? 'selected' : '' }}>
                        {{ $program->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-200 mb-2">Keterangan Tambahan (Opsional)</label>
            <input type="text" name="description" value="{{ old('description', $fee->description) }}" placeholder="Contoh: Sekali bayar di awal tahun"
                   class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
        </div>

        <div class="border-t border-gray-100 dark:border-slate-700 pt-6 flex justify-end">
            <button type="submit" class="bg-primary-600 text-white px-6 py-2.5 rounded-md hover:bg-green-800 transition shadow font-semibold">
                Perbarui Komponen Biaya
            </button>
        </div>
    </form>
</div>
@endsection
