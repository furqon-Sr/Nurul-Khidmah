<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Santri Baru (PPDB) - Pondok Pesantren Nurul Khidmah Lamongan</title>
    @include('partials.site-favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #F4F7F5;
        }
        .hp-field {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen flex flex-col justify-between antialiased text-gray-800">

    <!-- Header Navbar -->
    <x-frontend.navbar />

    <!-- Main Content Container -->
    <main class="flex-grow py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-8">

            <!-- Judul & Info Pondok -->
            <div class="text-center space-y-2">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-[#0D6B3A] tracking-tight">
                    Formulir Pendaftaran Calon Santri Baru
                </h1>
            </div>

            <!-- Pesan Sukses / Notifikasi Flash -->
            @if(session('success'))
                <div class="bg-emerald-50 border-l-4 border-[#0D6B3A] p-5 rounded-2xl shadow-sm space-y-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#0D6B3A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <h3 class="text-base font-bold text-[#0D6B3A]">Pendaftaran Berhasil Terkirim!</h3>
                    </div>
                    <p class="text-sm text-emerald-800">{{ session('success') }}</p>
                    <p class="text-xs text-gray-500">Panitia PPDB akan segera memverifikasi berkas Anda. Informasi nomor registrasi dan status dapat disimpan sebagai bukti.</p>
                </div>
            @endif

            <!-- Notifikasi Validasi Error -->
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-5 rounded-2xl shadow-sm space-y-2" role="alert">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-sm font-bold text-red-800">Mohon lengkapi bagian yang belum sesuai:</h3>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 text-xs sm:text-sm text-red-600">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Pendaftaran -->
            <form action="{{ route('registration.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8" x-data="{ sameAddress: false, studentAddress: '{{ old('residential_address', '') }}' }">
                @csrf
                <input type="hidden" name="branch" value="Lamongan">

                {{-- Honeypot anti-bot field --}}
                <div class="hp-field" aria-hidden="true">
                    <label for="website_url">Jangan diisi</label>
                    <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
                </div>

                {{-- Nomor Registrasi Virtual --}}
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-emerald-900/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor Registrasi Sistem</span>
                        <p class="text-xs text-gray-500 mt-0.5">Kode identitas pendaftaran unik yang diterbitkan secara otomatis.</p>
                    </div>
                    <div class="w-full sm:w-auto">
                        <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number', $registrationNumber) }}" readonly
                               class="w-full sm:w-56 min-h-11 rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-2.5 text-center font-mono font-bold text-[#0D6B3A] text-lg cursor-not-allowed">
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════════════ -->
                <!-- BAGIAN 1: DATA SISWA / CALON SANTRI                              -->
                <!-- ═══════════════════════════════════════════════════════════════════ -->
                <div class="bg-[#D5E2DB] rounded-3xl p-5 sm:p-6 md:p-8 space-y-6">
                    <div class="border-b border-[#0D6B3A]/20 pb-4">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#0D6B3A]">Data Siswa</h2>
                        <p class="text-xs sm:text-sm text-gray-700 mt-1">Lengkapi data pribadi calon santri sesuai dengan Akta Kelahiran atau Kartu Keluarga.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        {{-- 1. Nama Siswa --}}
                        <div class="md:col-span-2">
                            <label for="student_name" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Nama Lengkap Siswa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="student_name" name="student_name" value="{{ old('student_name') }}" placeholder="Contoh: Ach. Azyan Uwais Al Qorny" required autocomplete="name"
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm placeholder-gray-400 focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- 2. Jenis Kelamin --}}
                        <div>
                            <label for="gender" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <select id="gender" name="gender" required class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base font-medium shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki </option>
                                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan </option>
                            </select>
                        </div>

                        {{-- 3. Program Pendidikan Dituju (Sesuai yang dibuka Admin) --}}
                        <div>
                            <label for="target_class" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Program Pendidikan Dituju <span class="text-red-500">*</span>
                            </label>
                            <select id="target_class" name="target_class" required class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base font-medium shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                                <option value="" disabled {{ !old('target_class') && !$program ? 'selected' : '' }}>-- Pilih Program Pendidikan --</option>
                                @foreach($educationPrograms as $programOption)
                                    @php
                                        $isSelected = old('target_class') == $programOption->name ||
                                                      (strtolower((string) $program) == strtolower($programOption->slug));
                                    @endphp
                                    <option value="{{ $programOption->name }}"
                                            {{ $isSelected ? 'selected' : '' }}
                                            {{ !$programOption->is_registration_open ? 'disabled' : '' }}>
                                        {{ $programOption->name }}
                                        @if(!$programOption->is_registration_open)
                                            @if($programOption->is_coming_soon)
                                                (Akan Segera Hadir)
                                            @else
                                                (Pendaftaran Ditutup)
                                            @endif
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 4. Tempat Lahir --}}
                        <div>
                            <label for="birth_place" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Tempat Lahir <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="birth_place" name="birth_place" value="{{ old('birth_place', 'Lamongan') }}" placeholder="Contoh: Lamongan" required
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- 5. Tanggal Lahir --}}
                        <div>
                            <label for="birth_date" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required autocomplete="bday"
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- 6. Asal Sekolah --}}
                        <div>
                            <label for="previous_school" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Asal Sekolah (SD / MI) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="previous_school" name="previous_school" value="{{ old('previous_school') }}" placeholder="Contoh: MI Mansyaul Ulum Lamongan" required
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- 7. NISN --}}
                        <div>
                            <label for="nisn" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Nomor Induk Siswa Nasional (NISN)
                                <span class="text-xs font-normal text-gray-500">(10 Digit Angka)</span>
                            </label>
                            <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}" placeholder="Contoh: 0147404399" maxlength="15" inputmode="numeric"
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm font-mono focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- 8. Status Mukim --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Status Tinggal / Asrama <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-center gap-3 p-4 bg-white rounded-2xl cursor-pointer shadow-sm border-2 transition"
                                       :class="$el.querySelector('input').checked ? 'border-[#0D6B3A] bg-emerald-50/50' : 'border-transparent'">
                                    <input type="radio" name="boarding_status" value="Mukim" {{ old('boarding_status', 'Mukim') === 'Mukim' ? 'checked' : '' }} class="w-5 h-5 text-[#0D6B3A] focus:ring-[#0D6B3A]">
                                    <div>
                                        <span class="font-bold text-sm text-gray-900 block">Mukim di Pondok</span>
                                        <span class="text-xs text-gray-500">Tinggal dan bermalam di asrama/kobong pesantren</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-4 bg-white rounded-2xl cursor-pointer shadow-sm border-2 transition"
                                       :class="$el.querySelector('input').checked ? 'border-[#0D6B3A] bg-emerald-50/50' : 'border-transparent'">
                                    <input type="radio" name="boarding_status" value="Tidak Mukim" {{ old('boarding_status') === 'Tidak Mukim' ? 'checked' : '' }} class="w-5 h-5 text-[#0D6B3A] focus:ring-[#0D6B3A]">
                                    <div>
                                        <span class="font-bold text-sm text-gray-900 block">Tidak Mukim </span>
                                        <span class="text-xs text-gray-500">Pulang-pergi ke rumah setelah jam pelajaran madrasah</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- 9. Alamat Lengkap Siswa --}}
                        <div class="md:col-span-2">
                            <label for="residential_address" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Alamat Lengkap Siswa <span class="text-red-500">*</span>
                            </label>
                            <textarea id="residential_address" name="residential_address" rows="3" required x-model="studentAddress"
                                      placeholder="Contoh: Dusun Lopang, RT 01 / RW 01, Desa Lopang, Kec. Kembangbahu, Kab. Lamongan"
                                      class="w-full rounded-2xl bg-white border-0 p-4 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">{{ old('residential_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════════════ -->
                <!-- BAGIAN 2: DATA ORANG TUA / WALI SANTRI                             -->
                <!-- ═══════════════════════════════════════════════════════════════════ -->
                <div class="bg-[#D5E2DB] rounded-3xl p-5 sm:p-6 md:p-8 space-y-6">
                    <div class="border-b border-[#0D6B3A]/20 pb-4">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#0D6B3A]">Data Orang Tua / Wali Murid</h2>
                        <p class="text-xs sm:text-sm text-gray-700 mt-1">Data kontak ini digunakan untuk menerbitkan akun Portal Wali Santri &amp; pengiriman notifikasi WhatsApp resmi.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        {{-- Nama Ayah --}}
                        <div>
                            <label for="father_name" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Nama Lengkap Ayah Kandung <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="father_name" name="father_name" value="{{ old('father_name') }}" placeholder="Contoh: Zainal Abidin" required
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- Nama Ibu --}}
                        <div>
                            <label for="mother_name" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Nama Lengkap Ibu Kandung <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="mother_name" name="mother_name" value="{{ old('mother_name') }}" placeholder="Contoh: Siti Aminah" required
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- Pekerjaan Orang Tua --}}
                        <div>
                            <label for="guardian_occupation" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Pekerjaan Orang Tua / Wali <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="guardian_occupation" name="guardian_occupation" value="{{ old('guardian_occupation') }}" placeholder="Contoh: Petani / Wiraswasta / Guru / PNS" required
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- Nomor WhatsApp Wali --}}
                        <div>
                            <label for="student_phone" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Nomor WhatsApp Wali Santri (Aktif) <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="student_phone" name="student_phone" value="{{ old('student_phone') }}" placeholder="Contoh: 081234567890" required
                                   inputmode="tel" autocomplete="tel"
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                            <p class="text-[11px] text-[#0D6B3A]/80 mt-1">Pastikan nomor memiliki WhatsApp aktif untuk pengiriman bukti pendaftaran &amp; kata sandi.</p>
                        </div>

                        {{-- Email Wali --}}
                        <div class="md:col-span-2">
                            <label for="student_email" class="block text-xs font-bold text-[#0D6B3A] mb-2">
                                Alamat Email Wali
                                <span class="text-xs font-normal text-gray-500">(Opsional, untuk login alternatif Portal Wali)</span>
                            </label>
                            <input type="email" id="student_email" name="student_email" value="{{ old('student_email') }}" placeholder="Contoh: wali.santri@gmail.com"
                                   autocomplete="email" autocorrect="off" autocapitalize="off" spellcheck="false"
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        {{-- Alamat Orang Tua --}}
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-2">
                                <label for="guardian_address" class="block text-xs font-bold text-[#0D6B3A]">
                                    Alamat Tempat Tinggal Orang Tua / Wali
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-xs text-[#0D6B3A] font-semibold cursor-pointer">
                                    <input type="checkbox" x-model="sameAddress"
                                           @change="if(sameAddress) { document.getElementById('guardian_address').value = studentAddress; }"
                                           class="rounded text-[#0D6B3A] focus:ring-[#0D6B3A]">
                                    Sama dengan alamat siswa
                                </label>
                            </div>
                            <textarea id="guardian_address" name="guardian_address" rows="2"
                                      placeholder="Tuliskan alamat orang tua jika berbeda dengan tempat tinggal siswa..."
                                      class="w-full rounded-2xl bg-white border-0 p-4 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">{{ old('guardian_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════════════════ -->
                <!-- BAGIAN 3: DOKUMEN PERSYARATAN & PASFOTO                           -->
                <!-- ═══════════════════════════════════════════════════════════════════ -->
                <div class="bg-[#D5E2DB] rounded-3xl p-5 sm:p-6 md:p-8 space-y-6">
                    <div class="border-b border-[#0D6B3A]/20 pb-4">
                        <h2 class="text-xl sm:text-2xl font-bold text-[#0D6B3A]">Dokumen Persyaratan &amp; Pasfoto</h2>
                        <p class="text-xs sm:text-sm text-gray-700 mt-1">Unggah dokumen fisik dalam bentuk scan atau foto yang jelas (Format: PDF, JPG, PNG. Maksimal 3MB per berkas).</p>
                    </div>

                    <div class="space-y-6">
                        {{-- 1. Pasfoto 3:4 dengan Cropper --}}
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-900/10 image-crop-wrapper" data-field="student_photo">
                            <label for="student_photo" class="block text-xs font-bold text-[#0D6B3A] mb-1">
                                1. Pasfoto Calon Santri <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="student_photo" id="student_photo" required accept="image/jpeg,image/png,image/jpg,image/webp"
                                   class="image-crop-input w-full min-h-12 text-xs sm:text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0D6B3A] file:text-white hover:file:bg-emerald-800 border p-2 rounded-2xl bg-gray-50 shadow-sm cursor-pointer"
                                   data-aspect-ratio="0.75" data-aspect-label="3:4 (Pasfoto)">

                            <input type="hidden" name="student_photo_crop_x" value="{{ old('student_photo_crop_x') }}">
                            <input type="hidden" name="student_photo_crop_y" value="{{ old('student_photo_crop_y') }}">
                            <input type="hidden" name="student_photo_crop_width" value="{{ old('student_photo_crop_width') }}">
                            <input type="hidden" name="student_photo_crop_height" value="{{ old('student_photo_crop_height') }}">
                            <input type="hidden" id="student_photo_base64" name="student_photo_base64" value="{{ old('student_photo_base64') }}">

                            <div class="image-crop-status hidden mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 shadow-inner">
                                <p class="text-xs font-semibold text-emerald-800 mb-2"> Pasfoto 3x4 berhasil dipotong dan siap dikirim</p>
                                <img src="" alt="Pratinjau Pasfoto" class="image-crop-result-preview max-h-40 rounded-xl border border-emerald-200 object-contain bg-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                            {{-- 2. Kartu Keluarga (KK) --}}
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-900/10">
                                <label for="file_kk" class="block text-xs font-bold text-[#0D6B3A] mb-1">
                                    2. Kartu Keluarga
                                </label>
                                <input type="file" id="file_kk" name="file_kk" accept="application/pdf,image/jpeg,image/png,image/jpg"
                                       class="w-full min-h-12 text-xs text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0D6B3A] file:text-white hover:file:bg-emerald-800 border p-2 rounded-2xl bg-gray-50 shadow-sm cursor-pointer">
                            </div>

                            {{-- 3. Akta Kelahiran --}}
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-900/10">
                                <label for="file_akta" class="block text-xs font-bold text-[#0D6B3A] mb-1">
                                    3. Akta Kelahiran
                                </label>
                                <input type="file" id="file_akta" name="file_akta" accept="application/pdf,image/jpeg,image/png,image/jpg"
                                      class="w-full min-h-12 text-xs text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0D6B3A] file:text-white hover:file:bg-emerald-800 border p-2 rounded-2xl bg-gray-50 shadow-sm cursor-pointer">
                            </div>

                            {{-- 4. KTP Orang Tua / Wali --}}
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-900/10">
                                <label for="file_ktp" class="block text-xs font-bold text-[#0D6B3A] mb-1">
                                    4. KTP Orang Tua
                                </label>
                                <input type="file" id="file_ktp" name="file_ktp" accept="application/pdf,image/jpeg,image/png,image/jpg"
                                       class="w-full min-h-12 text-xs text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0D6B3A] file:text-white hover:file:bg-emerald-800 border p-2 rounded-2xl bg-gray-50 shadow-sm cursor-pointer">
                            </div>

                            {{-- 5. Ijazah / SKL --}}
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-900/10">
                                <label for="file_ijazah" class="block text-xs font-bold text-[#0D6B3A] mb-1">
                                    5. Ijazah / Surat Keterangan Lulus
                                </label>
                                <input type="file" id="file_ijazah" name="file_ijazah" accept="application/pdf,image/jpeg,image/png,image/jpg"
                                       class="w-full min-h-12 text-xs text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0D6B3A] file:text-white hover:file:bg-emerald-800 border p-2 rounded-2xl bg-gray-50 shadow-sm cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit & Pernyataan Resmi -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-emerald-900/10 space-y-5 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="max-w-xl">
                        <h4 class="font-bold text-sm text-[#0D6B3A]">Pernyataan Kebenaran Data</h4>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Dengan menekan tombol di samping, Anda menyatakan bahwa seluruh data yang diisikan adalah sah dan benar, serta bersedia mengikuti tata tertib penerimaan santri baru di Pondok Pesantren Nurul Khidmah Lamongan.
                        </p>
                    </div>

                    <button type="submit" class="w-full sm:w-auto min-h-12 inline-flex items-center justify-center px-8 py-4 rounded-md bg-[#E8F81D] hover:bg-yellow-400 text-gray-900 font-extrabold text-base transition shadow-lg hover:shadow-xl shrink-0">
                        Kirim Formulir PPDB &gt;
                    </button>
                </div>

            </form>

        </div>
    </main>

    <!-- Footer Component -->
    <x-frontend.footer />

    @include('partials.image-cropper-modal')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const fileInput = document.getElementById('student_photo');
            const base64Input = document.getElementById('student_photo_base64');
            const previewImg = document.querySelector('.image-crop-result-preview');

            form.addEventListener('submit', function(e) {
                if (previewImg && previewImg.src && previewImg.src.startsWith('data:image')) {
                    base64Input.value = previewImg.src;
                }

                if (!base64Input.value) {
                    e.preventDefault();
                    alert('Silakan selesaikan crop Pasfoto Calon Santri terlebih dahulu — pilih gambar, atur area 3:4, lalu klik Terapkan crop.');
                    fileInput.focus();
                    return;
                }

                fileInput.value = '';
            });
        });
    </script>
</body>
</html>
