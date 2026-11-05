<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Santri Baru - Pondok Pesantren Nurul Khidmah Lamongan</title>
    @include('partials.site-favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
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
<body class="bg-gray-50 font-sans text-gray-900 antialiased min-h-dvh flex flex-col">

    <!-- Navbar Component -->
    <x-frontend.navbar />

    <main class="py-12 md:py-20 bg-white flex-1">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">

            <!-- Title -->
            <div class="mb-10">
                <span class="inline-block px-3.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-[#0D6B3A] text-xs font-bold mb-3">
                    PSB Online — Pondok Pesantren Nurul Khidmah Lamongan
                </span>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#0D6B3A] mb-3">
                    Formulir Pendaftaran Santri Baru
                </h1>
                <p class="text-gray-600 text-sm md:text-base leading-relaxed">
                    Silakan lengkapi seluruh formulir data di bawah ini secara benar dan valid untuk memulai proses penerimaan santri baru di Pondok Pesantren Nurul Khidmah (Dusun Wringinanom, Desa Lopang, Kec. Kembangbahu, Kab. Lamongan).
                </p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div role="status" class="mb-8 p-6 bg-green-50 border-l-4 border-[#0D6B3A] text-[#0D6B3A] rounded-xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-bold text-lg">Pendaftaran Berhasil Dikirim!</span>
                    </div>
                    <p class="mt-2 text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Errors validation -->
            @if($errors->any())
                <div role="alert" class="mb-8 p-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-6 h-6 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="font-bold text-lg">Terdapat beberapa kesalahan input:</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 text-sm text-red-600">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('registration.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <input type="hidden" name="branch" value="Lamongan">

                {{-- Honeypot anti-bot field --}}
                <div class="hp-field" aria-hidden="true">
                    <label for="website_url">Jangan diisi</label>
                    <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
                </div>

                <!-- CARD 1: Pendaftaran Terpadu -->
                <div class="bg-[#D5E2DB] rounded-3xl p-5 sm:p-6 md:p-8 space-y-6">
                    <h2 class="text-[#0D6B3A] font-bold text-base sm:text-lg tracking-wide">1. Identitas Registrasi &amp; Pasfoto</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <div class="md:col-span-2">
                            <label for="registration_number" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nomor Virtual Pendaftaran</label>
                            <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number', $registrationNumber) }}" readonly
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base sm:text-sm shadow-sm font-mono font-bold text-gray-700 cursor-not-allowed">
                        </div>
                        <div class="image-crop-wrapper md:col-span-2" data-field="student_photo">
                            <label for="student_photo" class="block text-xs font-semibold text-[#0D6B3A] mb-1">Unggah Pasfoto Calon Santri (Rasio 3:4)</label>
                            <p class="text-xs text-[#0D6B3A]/85 mb-2 font-medium">Format: JPG, PNG, WEBP. Maks. 20MB. Pilih foto, lalu potong (crop) pada jendela yang muncul.</p>
                            <input type="file" name="student_photo" id="student_photo" required accept="image/jpeg,image/png,image/jpg,image/webp"
                                   class="image-crop-input w-full min-h-12 text-xs sm:text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-[#0D6B3A] file:text-white hover:file:bg-emerald-800 border-0 p-2.5 rounded-2xl bg-white shadow-sm cursor-pointer"
                                   data-aspect-ratio="0.75" data-aspect-label="3:4 (Pasfoto)">

                            <input type="hidden" name="student_photo_crop_x" value="{{ old('student_photo_crop_x') }}">
                            <input type="hidden" name="student_photo_crop_y" value="{{ old('student_photo_crop_y') }}">
                            <input type="hidden" name="student_photo_crop_width" value="{{ old('student_photo_crop_width') }}">
                            <input type="hidden" name="student_photo_crop_height" value="{{ old('student_photo_crop_height') }}">
                            <input type="hidden" id="student_photo_base64" name="student_photo_base64" value="{{ old('student_photo_base64') }}">

                            <div class="image-crop-status hidden mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 shadow-inner">
                                <p class="text-xs font-semibold text-emerald-800 mb-2">✓ Pasfoto berhasil dipotong &amp; siap dikirim</p>
                                <img src="" alt="Pratinjau Pasfoto" class="image-crop-result-preview max-h-48 rounded-xl border border-emerald-200 object-contain bg-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Data Calon Santri -->
                <div class="bg-[#D5E2DB] rounded-3xl p-5 sm:p-6 md:p-8 space-y-6">
                    <h2 class="text-[#0D6B3A] font-bold text-base sm:text-lg tracking-wide">2. Biodata Calon Santri</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <div class="md:col-span-2">
                            <label for="student_name" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nama Lengkap Calon Santri (Sesuai Akta / KK)</label>
                            <input type="text" id="student_name" name="student_name" value="{{ old('student_name') }}" placeholder="Contoh: Ahmad Zaini Dahlan" required autocomplete="name"
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm placeholder-gray-400 focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        <div>
                            <label for="gender" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Jenis Kelamin</label>
                            <select id="gender" name="gender" required class="w-full min-h-12 rounded-2xl bg-[#E8F81D] border-0 px-4 py-3 text-base font-medium shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki (Putra)</option>
                                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan (Putri)</option>
                            </select>
                        </div>

                        <div>
                            <label for="birth_date" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required autocomplete="bday"
                                   class="w-full min-h-12 rounded-2xl bg-[#E8F81D] border-0 px-4 py-3 text-base font-medium shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        <div class="md:col-span-2">
                            <label for="target_class" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Jenjang / Program Pendidikan Dituju</label>
                            <select id="target_class" name="target_class" required class="w-full min-h-12 rounded-2xl bg-[#E8F81D] border-0 px-4 py-3 text-base font-medium shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                                <option value="" disabled {{ !old('target_class') && !$program ? 'selected' : '' }}>-- Pilih Jenjang Pendidikan --</option>
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
                                                (Akan Segera Hadir / Tahap Perencanaan)
                                            @else
                                                (Pendaftaran Ditutup)
                                            @endif
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: Kontak & Alamat Domisili -->
                <div class="bg-[#D5E2DB] rounded-3xl p-5 sm:p-6 md:p-8 space-y-6">
                    <h2 class="text-[#0D6B3A] font-bold text-base sm:text-lg tracking-wide">3. Kontak Wali &amp; Alamat Domisili</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <div>
                            <label for="student_email" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Alamat Email Aktif Wali / Pendaftar</label>
                            <input type="email" id="student_email" name="student_email" value="{{ old('student_email') }}" placeholder="Contoh: wali.santri@gmail.com" required
                                   autocomplete="email" autocorrect="off" autocapitalize="off" spellcheck="false"
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        <div>
                            <label for="student_phone" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nomor WhatsApp Wali (Aktif)</label>
                            <input type="tel" id="student_phone" name="student_phone" value="{{ old('student_phone') }}" placeholder="Contoh: 081234567890" required
                                   inputmode="tel" autocomplete="tel" autocorrect="off" spellcheck="false"
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        <div class="md:col-span-2">
                            <label for="residential_address" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Alamat Lengkap Domisili Rumah</label>
                            <textarea id="residential_address" name="residential_address" rows="3" placeholder="Tuliskan Dusun/Jalan, RT/RW, Desa/Kelurahan, Kecamatan, Kabupaten..." required autocomplete="street-address"
                                      class="w-full rounded-2xl bg-white border-0 p-4 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">{{ old('residential_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: Data Orang Tua Kandung -->
                <div class="bg-[#D5E2DB] rounded-3xl p-5 sm:p-6 md:p-8 space-y-6">
                    <h2 class="text-[#0D6B3A] font-bold text-base sm:text-lg tracking-wide">4. Data Orang Tua Calon Santri</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                        <div>
                            <label for="father_name" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nama Lengkap Ayah Kandung</label>
                            <input type="text" id="father_name" name="father_name" value="{{ old('father_name') }}" placeholder="Nama Lengkap Ayah Kandung" required
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>

                        <div>
                            <label for="mother_name" class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nama Lengkap Ibu Kandung</label>
                            <input type="text" id="mother_name" name="mother_name" value="{{ old('mother_name') }}" placeholder="Nama Lengkap Ibu Kandung" required
                                   class="w-full min-h-12 rounded-2xl bg-white border-0 px-4 py-3 text-base shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>
                    </div>
                </div>

                <!-- Submit Button and Disclaimer -->
                <div class="space-y-5 pt-2">
                    <div>
                        <button type="submit" class="w-full sm:w-auto min-h-12 inline-flex items-center justify-center px-8 py-4 rounded-full bg-[#E8F81D] hover:bg-yellow-400 text-gray-900 font-bold text-base transition shadow-lg hover:shadow-xl">
                            Kirim Formulir Pendaftaran Sekarang &gt;
                        </button>
                    </div>

                    <p class="text-xs text-gray-500 leading-relaxed max-w-3xl">
                        Dengan mengirimkan formulir ini, Anda menyatakan bahwa seluruh data yang diisi benar dan bersedia mengikuti seluruh prosedur penerimaan santri baru di Pondok Pesantren Nurul Khidmah Lamongan secara resmi.
                    </p>
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
                    alert('Silakan selesaikan crop Pasfoto Calon Santri terlebih dahulu — pilih gambar, atur area, lalu klik Terapkan crop.');
                    fileInput.focus();
                    return;
                }

                fileInput.value = '';
            });
        });
    </script>
</body>
</html>
