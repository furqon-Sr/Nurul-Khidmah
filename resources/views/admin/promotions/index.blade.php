@extends('layouts.admin')

@section('title', 'Kenaikan Kelas & Kelulusan Santri')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Kenaikan Kelas &amp; Kelulusan Kolektif Santri</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Pindahkan santri secara massal ke kelas tahun ajaran berikutnya atau tetapkan kelulusan tingkat akhir. NIS &amp; Akun Portal Wali tetap utuh beserta arsip rapor semester sebelumnya.
        </p>
    </div>
    @if($activeYear)
        <span class="px-2.5.5 py-1 text-2xs uppercase tracking-wide font-bold rounded-md bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 w-fit">
            Tahun Ajaran Aktif: {{ $activeYear->nama_tahun_ajaran }} ({{ $activeYear->semester }})
        </span>
    @endif
</div>

@if(session('success'))
    <div role="status" class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/50 border-l-4 border-emerald-600 text-emerald-800 dark:text-emerald-200 rounded-xl shadow-sm text-sm font-medium">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div role="alert" class="mb-6 p-4 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 text-red-700 dark:text-red-200 rounded-xl shadow-sm text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Filter Kelas Asal --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
    <form method="GET" action="{{ route('admin.promotions.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label for="asal_kelas_id" class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                1. Pilih Kelas Asal (Sumber Santri)
            </label>
            <select id="asal_kelas_id" name="asal_kelas_id" onchange="this.form.submit()"
                    class="w-full min-h-11 px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-semibold">
                @forelse($allClasses as $c)
                    <option value="{{ $c->id }}" {{ optional($asalKelas)->id === $c->id ? 'selected' : '' }}>
                        {{ $c->nama_kelas }} — TA {{ $c->tahunAjaran->nama_tahun_ajaran ?? '-' }} ({{ $c->active_santris_count }} Santri Aktif)
                    </option>
                @empty
                    <option value="">Belum ada data kelas</option>
                @endforelse
            </select>
        </div>

        <div>
            <label for="search" class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                Cari Cepat Santri di Kelas Ini
            </label>
            <input type="text" id="search" name="search" value="{{ request('search') }}"
                   autocorrect="off" autocapitalize="off" spellcheck="false"
                   placeholder="Ketik Nama atau NIS..."
                   class="w-full min-h-11 px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="min-h-11 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                Tampilkan Daftar
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.promotions.index', ['asal_kelas_id' => optional($asalKelas)->id]) }}"
                   class="min-h-11 inline-flex items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

@if($asalKelas)
<form method="POST" action="{{ route('admin.promotions.store') }}" x-data="{ jenisAksi: '{{ old('jenis_aksi', 'Naik Kelas') }}' }">
    @csrf
    <input type="hidden" name="asal_kelas_id" value="{{ $asalKelas->id }}">

    {{-- Panel Keputusan Kenaikan / Kelulusan --}}
    <div class="bg-emerald-50/70 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800 p-5 mb-6">
        <h2 class="text-sm font-bold text-emerald-900 dark:text-emerald-200 uppercase tracking-wider mb-3">
            2. Tentukan Keputusan Akademik Kolektif
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="jenis_aksi" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Jenis Keputusan</label>
                <select id="jenis_aksi" name="jenis_aksi" x-model="jenisAksi"
                        class="w-full min-h-11 px-3.5 py-2.5 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-gray-900 text-sm font-bold text-emerald-900 dark:text-emerald-200">
                    <option value="Naik Kelas">⬆️ Naik Kelas (Tahun Ajaran Baru)</option>
                    <option value="Tinggal Kelas">↩️ Tinggal / Mengulang Kelas</option>
                    <option value="Pindah Kelas">⇄ Pindah Rombel / Kelas Paralel</option>
                    <option value="Lulus">🎓 Lulus Tingkat Akhir (Jadi Alumni)</option>
                </select>
            </div>

            <div x-show="jenisAksi !== 'Lulus'">
                <label for="tujuan_kelas_id" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kelas Tujuan</label>
                <select id="tujuan_kelas_id" name="tujuan_kelas_id" :required="jenisAksi !== 'Lulus'"
                        class="w-full min-h-11 px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm font-semibold">
                    <option value="">-- Pilih Kelas Tujuan --</option>
                    @foreach($allClasses as $target)
                        @if($target->id !== $asalKelas->id)
                            <option value="{{ $target->id }}" {{ (int) old('tujuan_kelas_id') === $target->id ? 'selected' : '' }}>
                                {{ $target->nama_kelas }} (Tingkat {{ $target->tingkat_kelas }} — TA {{ $target->tahunAjaran->nama_tahun_ajaran ?? '-' }} {{ $target->tahunAjaran->semester ?? '' }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div x-show="jenisAksi === 'Lulus'" x-cloak class="flex items-center">
                <div class="w-full p-3 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200">
                    Status santri terpilih akan diubah menjadi <strong>Lulus</strong>. Akun Wali Santri &amp; NIS tetap aktif untuk mengunduh arsip Rapor.
                </div>
            </div>

            <div>
                <label for="catatan" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Catatan SK / Keterangan (Opsional)</label>
                <input type="text" id="catatan" name="catatan" value="{{ old('catatan') }}"
                       placeholder="Contoh: Rapat Pleno Kenaikan Kelas Genap"
                       class="w-full min-h-11 px-3.5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm">
            </div>
        </div>
    </div>

    {{-- Tabel Daftar Santri Kelas Asal --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 id="promotion-table-heading" class="font-bold text-gray-900 dark:text-gray-100 text-base">
                    Daftar Santri Aktif — {{ $asalKelas->nama_kelas }} ({{ $santris->count() }} Santri)
                </h3>
                <p class="text-xs text-gray-600">Centang santri yang memenuhi syarat kenaikan kelas atau kelulusan.</p>
            </div>

            @if($santris->isNotEmpty())
                <button type="submit"
                        onclick="return confirm('Proses keputusan akademik untuk seluruh santri yang dicentang?')"
                        class="min-h-11 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow transition">
                    Proses Santri Terpilih →
                </button>
            @endif
        </div>

        <div tabindex="0" role="region" aria-labelledby="promotion-table-heading" class="overflow-x-auto max-h-[65vh]">
            <table class="table-builder min-w-full border-separate border-spacing-0">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center w-12 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 sticky left-0 z-20">
                            <input type="checkbox" id="check-all-promo" checked class="h-5 w-5 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500 cursor-pointer" aria-label="Pilih semua santri">
                        </th>
                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">NIS &amp; Nama Santri</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Mapel Dinilai</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Rata-Rata Semester</th>
                        <th scope="col" class="px-4 py-3.5 text-center text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Sikap</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Akun Portal Wali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($santris as $st)
                        @php
                            $stat = $gpaMap[$st->id] ?? ['count' => 0, 'avg' => null];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                            <td class="px-4 py-3.5 text-center bg-white dark:bg-gray-800 sticky left-0 z-[1] border-b border-gray-100 dark:border-gray-700/60">
                                <input type="checkbox" name="santri_ids[]" value="{{ $st->id }}" checked
                                       class="promo-checkbox h-5 w-5 rounded border-gray-300 text-emerald-700 focus:ring-emerald-500 cursor-pointer"
                                       aria-label="Pilih {{ $st->nama_lengkap }}">
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap border-b border-gray-100 dark:border-gray-700/60">
                                <div class="font-bold text-gray-900 dark:text-gray-100 text-sm">{{ $st->nama_lengkap }}</div>
                                <div class="text-xs font-mono text-gray-600">NIS: {{ $st->nis }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap text-xs font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700/60">
                                {{ $stat['count'] }} Mapel
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap border-b border-gray-100 dark:border-gray-700/60">
                                @if($stat['avg'] !== null)
                                    <span class="px-2.5 py-1 rounded-md text-2xs uppercase tracking-wide font-bold {{ $stat['avg'] >= 65 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ number_format($stat['avg'], 2) }} ({{ \App\Models\Nilai::calculateGrade($stat['avg']) }})
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Belum ada nilai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap text-xs text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700/60">
                                {{ $st->nilai_sikap ?: '-' }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700/60">
                                @if($st->user)
                                    <span class="text-emerald-700 dark:text-emerald-400 font-semibold">✓ Aktif</span> ({{ $st->user->email }})
                                @else
                                    <span class="text-gray-400">Belum ditautkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-600">
                                Tidak ada santri aktif pada kelas ini. Buat kelas tahun ajaran baru terlebih dahulu di menu <strong>Data Kelas</strong> jika ingin mempromosikan santri.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>
@endif

{{-- Riwayat Promosi & Kelulusan Terbaru --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
        <h3 id="history-promo-heading" class="font-bold text-gray-800 dark:text-gray-100 text-sm uppercase tracking-wider">
            Log Riwayat Kenaikan Kelas &amp; Kelulusan Terakhir
        </h3>
    </div>
    <div tabindex="0" role="region" aria-labelledby="history-promo-heading" class="overflow-x-auto">
        <table class="table-builder min-w-full border-separate border-spacing-0 text-xs">
            <thead>
                <tr>
                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Waktu</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Santri</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Keputusan</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Perpindahan Kelas</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-600 border-b border-gray-200 dark:border-gray-700">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($recentLogs as $log)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $log->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap font-semibold text-gray-900 dark:text-gray-100">
                            {{ $log->santri->nama_lengkap ?? '-' }} ({{ $log->santri->nis ?? '-' }})
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded font-bold {{ $log->jenis_aksi === 'Lulus' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $log->jenis_aksi }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-300">
                            {{ $log->kelasLama->nama_kelas ?? 'Awal' }} → <strong>{{ $log->kelasBaru->nama_kelas ?? 'Alumni (Lulus)' }}</strong>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $log->catatan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-600">Belum ada riwayat kenaikan kelas atau kelulusan tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('check-all-promo');
        const boxes = document.querySelectorAll('.promo-checkbox');
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                boxes.forEach(cb => cb.checked = checkAll.checked);
            });
        }
    });
</script>
@endsection
