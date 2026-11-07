@extends('layouts.admin')

@section('title', 'Penjadwalan Pelajaran')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Penjadwalan Pelajaran</h1>
    </div>
    <a href="{{ route('admin.schedules.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition shadow-sm font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Plotting Jadwal Baru
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-4 bg-emerald-100 dark:bg-emerald-950/50 border-l-4 border-emerald-500 text-emerald-700 dark:text-emerald-300 rounded shadow-sm text-sm">
    {{ session('success') }}
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="flex flex-wrap items-center gap-3">
            <select name="tahun_ajaran_id" onchange="this.form.submit()" class="min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="all" {{ (string)($selectedTahunAjaranId ?? '') === 'all' ? 'selected' : '' }}>-- Semua Thn Ajaran --</option>
                @foreach($tahunAjarans as $ta)
                    <option value="{{ $ta->id }}" {{ (string)($selectedTahunAjaranId ?? '') === (string)$ta->id ? 'selected' : '' }}>
                        {{ $ta->nama_tahun_ajaran }} ({{ $ta->semester }}){{ $ta->status_aktif ? ' - Aktif' : '' }}
                    </option>
                @endforeach
            </select>

            <select name="kelas_id" onchange="this.form.submit()" class="min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">-- Semua Kelas --</option>
                @foreach($kelasses as $k)
                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kelas }} ({{ $k->tahunAjaran->nama_tahun_ajaran ?? '-' }})
                    </option>
                @endforeach
            </select>

            <select name="hari" onchange="this.form.submit()" class="min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">-- Semua Hari --</option>
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                    <option value="{{ $h }}" {{ request('hari') == $h ? 'selected' : '' }}>{{ $h }}</option>
                @endforeach
            </select>

            <select name="pengajar_id" onchange="this.form.submit()" class="min-h-11 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">-- Semua Ustaz / Pengajar --</option>
                @foreach($pengajars as $p)
                    <option value="{{ $p->id }}" {{ request('pengajar_id') == $p->id ? 'selected' : '' }}>{{ $p->nama_lengkap }}</option>
                @endforeach
            </select>

            <button type="submit" class="min-h-11 px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition shadow-sm">Filter</button>
            @if(request('tahun_ajaran_id') || request('kelas_id') || request('hari') || request('pengajar_id'))
                <a href="{{ route('admin.schedules.index', ['tahun_ajaran_id' => 'all']) }}" class="min-h-11 px-3 py-2 inline-flex items-center text-xs text-gray-600 dark:text-gray-400 hover:text-emerald-600 hover:underline">Reset Filter</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table-builder min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Hari & Jam</th>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Kelas / Ruang</th>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Mata Pelajaran</th>
                    <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-400">Pengajar (Ustaz/Ustazah)</th>
                    <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-600 dark:text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($schedules as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-block px-2.5 py-1 text-xs font-bold rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                            {{ $item->hari }}
                        </span>
                        <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-1">
                            {{ date('H:i', strtotime($item->waktu_mulai)) }} - {{ date('H:i', strtotime($item->waktu_selesai)) }} WIB
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $item->kelas->nama_kelas ?? '-' }}</div>
                        <div class="text-xs text-gray-600">Ruang: {{ $item->ruang ?? 'Kelas Utama' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-semibold text-emerald-700 dark:text-emerald-400">{{ $item->mataPelajaran->nama_mapel ?? '-' }}</div>
                        <div class="text-xs text-gray-600 font-mono">{{ $item->mataPelajaran->kode_mapel ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200 font-medium">
                        {{ $item->pengajar->nama_lengkap ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <x-icon-button type="edit" href="{{ route('admin.schedules.edit', $item->id) }}" class="mr-1.5" />
                        <form action="{{ route('admin.schedules.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                            @csrf
                            @method('DELETE')
                            <x-icon-button type="delete" :submit="true" />
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-600 dark:text-gray-400 text-sm">
                        Belum ada jadwal pelajaran terdaftar pada filter ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($schedules->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        {{ $schedules->links() }}
    </div>
    @endif
</div>
@endsection
