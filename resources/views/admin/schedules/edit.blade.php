@extends('layouts.admin')

@section('title', 'Edit Plotting Jadwal')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Plotting Jadwal Pelajaran</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Perbarui alokasi jam, pengajar, atau kelas.</p>
        </div>
        <a href="{{ route('admin.schedules.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm font-medium">
            ← Kembali
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('admin.schedules.update', $schedule->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun Ajaran & Semester <span class="text-rose-500">*</span></label>
                <select name="tahun_ajaran_id" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                    @foreach($tahunAjarans as $ta)
                        <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $schedule->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
                            {{ $ta->nama_tahun_ajaran }} (Semester {{ $ta->semester }}) {{ $ta->status_aktif ? '- AKTIF' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('tahun_ajaran_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kelas <span class="text-rose-500">*</span></label>
                    <select name="kelas_id" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasses as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id', $schedule->kelas_id) == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }} ({{ $k->kode_kelas }})</option>
                        @endforeach
                    </select>
                    @error('kelas_id') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select name="mata_pelajaran_id" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ old('mata_pelajaran_id', $schedule->mata_pelajaran_id) == $s->id ? 'selected' : '' }}>{{ $s->nama_mapel }} ({{ $s->kode_mapel }})</option>
                        @endforeach
                    </select>
                    @error('mata_pelajaran_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tenaga Pengajar (Ustaz / Ustazah) <span class="text-rose-500">*</span></label>
                <select name="pengajar_id" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                    <option value="">-- Pilih Ustaz / Ustazah --</option>
                    @foreach($pengajars as $p)
                        <option value="{{ $p->id }}" {{ old('pengajar_id', $schedule->pengajar_id) == $p->id ? 'selected' : '' }}>{{ $p->nama_lengkap }} ({{ $p->nomor_identitas }})</option>
                    @endforeach
                </select>
                @error('pengajar_id') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hari Pelaksanaan <span class="text-rose-500">*</span></label>
                    <select name="hari" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h)
                            <option value="{{ $h }}" {{ old('hari', $schedule->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('hari') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu Mulai <span class="text-rose-500">*</span></label>
                    <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', date('H:i', strtotime($schedule->waktu_mulai))) }}" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                    @error('waktu_mulai') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Waktu Selesai <span class="text-rose-500">*</span></label>
                    <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai', date('H:i', strtotime($schedule->waktu_selesai))) }}" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
                    @error('waktu_selesai') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ruangan / Gedung Pembelajaran</label>
                <input type="text" name="ruang" value="{{ old('ruang', $schedule->ruang) }}"
                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                <textarea name="keterangan" rows="2" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-emerald-500 text-sm">{{ old('keterangan', $schedule->keterangan) }}</textarea>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.schedules.index') }}" class="px-5 py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 text-sm font-medium">Batal</a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition text-sm font-medium shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
