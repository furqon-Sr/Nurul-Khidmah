@extends('layouts.admin')

@section('title', 'Edit Data Pendaftar PPDB')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.registrations.index') }}"
               class="inline-flex size-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition"
               aria-label="Kembali ke daftar pendaftar">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Data Pendaftar</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">No. Registrasi: <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $registration->registration_number }}</span></p>
            </div>
        </div>
        <a href="{{ route('admin.registrations.show', $registration->id) }}"
           class="h-10 px-3.5 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 transition">
            Lihat Detail Lengkap
        </a>
    </div>

    @if($errors->any())
        <div role="alert" class="mb-5 rounded-xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-800 dark:bg-error-950/40 dark:text-error-300">
            <p class="font-semibold mb-1">Terdapat kesalahan input:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.registrations.update', $registration->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Bagian 1: Data Calon Santri --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">1. Data Pribadi Calon Santri</h2>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="student_name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap <span class="text-error-500">*</span></label>
                    <input type="text" id="student_name" name="student_name" value="{{ old('student_name', $registration->student_name) }}" required
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label for="nisn" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">NISN</label>
                    <input type="text" id="nisn" name="nisn" value="{{ old('nisn', $registration->nisn) }}" maxlength="20"
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label for="gender" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jenis Kelamin <span class="text-error-500">*</span></label>
                    <select id="gender" name="gender" required
                            class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                        <option value="L" @selected(old('gender', $registration->gender) === 'L')>Laki-laki (Putra)</option>
                        <option value="P" @selected(old('gender', $registration->gender) === 'P')>Perempuan (Putri)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="birth_place" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tempat Lahir</label>
                        <input type="text" id="birth_place" name="birth_place" value="{{ old('birth_place', $registration->birth_place) }}"
                               class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label for="birth_date" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Lahir <span class="text-error-500">*</span></label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $registration->birth_date ? \Carbon\Carbon::parse($registration->birth_date)->format('Y-m-d') : '') }}" required
                               class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                </div>

                <div>
                    <label for="target_class" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pilihan Program Pendidikan <span class="text-error-500">*</span></label>
                    <select id="target_class" name="target_class" required
                            class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                        @foreach($classes as $c)
                            <option value="{{ $c }}" @selected(old('target_class', $registration->target_class) === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="academic_year" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tahun Ajaran <span class="text-error-500">*</span></label>
                        <select id="academic_year" name="academic_year" required
                                class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @foreach($academicYears as $year)
                                <option value="{{ $year }}" @selected(old('academic_year', $registration->academic_year) === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="boarding_status" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status Asrama <span class="text-error-500">*</span></label>
                        <select id="boarding_status" name="boarding_status" required
                                class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            <option value="Mukim" @selected(old('boarding_status', $registration->boarding_status ?? 'Mukim') === 'Mukim')>Mukim</option>
                            <option value="Tidak Mukim" @selected(old('boarding_status', $registration->boarding_status) === 'Tidak Mukim')>Tidak Mukim</option>
                        </select>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="previous_school" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Asal Sekolah</label>
                    <input type="text" id="previous_school" name="previous_school" value="{{ old('previous_school', $registration->previous_school) }}"
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div class="sm:col-span-2">
                    <label for="residential_address" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat Domisili Santri <span class="text-error-500">*</span></label>
                    <textarea id="residential_address" name="residential_address" rows="2" required
                              class="w-full rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">{{ old('residential_address', $registration->residential_address) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Bagian 2: Data Orang Tua / Wali --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">2. Data Orang Tua / Wali</h2>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="father_name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Ayah Kandung</label>
                    <input type="text" id="father_name" name="father_name" value="{{ old('father_name', $registration->father_name) }}"
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label for="mother_name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Ibu Kandung</label>
                    <input type="text" id="mother_name" name="mother_name" value="{{ old('mother_name', $registration->mother_name) }}"
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label for="guardian_name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Wali Utama (Tercantum) <span class="text-error-500">*</span></label>
                    <input type="text" id="guardian_name" name="guardian_name" value="{{ old('guardian_name', $registration->guardian_name) }}" required
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label for="guardian_occupation" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pekerjaan Orang Tua / Wali</label>
                    <input type="text" id="guardian_occupation" name="guardian_occupation" value="{{ old('guardian_occupation', $registration->guardian_occupation) }}"
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label for="guardian_phone" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. WhatsApp Wali <span class="text-error-500">*</span></label>
                    <input type="text" id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $registration->guardian_phone) }}" required
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div>
                    <label for="guardian_email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email Wali</label>
                    <input type="email" id="guardian_email" name="guardian_email" value="{{ old('guardian_email', $registration->guardian_email) }}"
                           class="w-full h-[42px] rounded-lg border border-gray-200 bg-white px-3.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>

                <div class="sm:col-span-2">
                    <label for="guardian_address" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat Orang Tua / Wali</label>
                    <textarea id="guardian_address" name="guardian_address" rows="2"
                              class="w-full rounded-lg border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">{{ old('guardian_address', $registration->guardian_address) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-1">
            <a href="{{ route('admin.registrations.index') }}"
               class="h-[42px] px-4 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                Batal
            </a>
            <button type="submit"
                    class="h-[42px] px-5 inline-flex items-center justify-center rounded-lg bg-primary-600 hover:bg-primary-700 text-sm font-semibold text-white shadow-xs transition cursor-pointer">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
