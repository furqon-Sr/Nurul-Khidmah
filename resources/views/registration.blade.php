<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Santri Baru - Pondok Pesantren Nurul Khidmah</title>
    @include('partials.site-favicon')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
    <!-- AlpineJS for interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    
    <!-- Navbar Component -->
    <x-frontend.navbar />

    <main class="py-16 md:py-24 bg-white min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
            
            <!-- Title -->
            <div class="mb-12">
                <h1 class="text-3xl md:text-4xl font-bold text-[#0D6B3A] mb-4">
                    Formulir Pendaftaran Santri Baru
                </h1>
                <p class="text-gray-600 text-sm md:text-base leading-relaxed">
                    Silakan lengkapi seluruh formulir data di bawah ini secara benar dan valid untuk memulai proses seleksi penerimaan santri baru Pondok Pesantren Nurul Khidmah tahun ajaran baru.
                </p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-8 p-6 bg-green-50 border-l-4 border-[#0D6B3A] text-[#0D6B3A] rounded-xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-bold text-lg">Pendaftaran Berhasil dikirim!</span>
                    </div>
                    <p class="mt-2 text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Errors validation -->
            @if($errors->any())
                <div class="mb-8 p-6 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl shadow-sm">
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
            <form action="{{ route('registration.store') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
                @csrf

                <!-- CARD 1: Pendaftaran Terpadu -->
                <div class="bg-[#D5E2DB] rounded-3xl p-6 md:p-8 space-y-6">
                    <h3 class="text-[#0D6B3A] font-bold text-md tracking-wide">Pendaftaran Terpadu Nurul Khidmah</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Pilih Cabang Pesantren</label>
                            <select name="branch" required class="w-full rounded-2xl bg-white border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A] appearance-none">
                                <option value="Surabaya" {{ old('branch') == 'Surabaya' ? 'selected' : '' }}>Surabaya</option>
                                <option value="Lamongan" {{ old('branch') == 'Lamongan' ? 'selected' : '' }}>Lamongan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nomor Virtual Pendaftaran</label>
                            <input type="text" name="registration_number" value="{{ old('registration_number', $registrationNumber) }}" readonly class="w-full rounded-2xl bg-white border-0 p-3.5 text-sm shadow-sm font-mono text-gray-600 cursor-not-allowed">
                        </div>
                        <div class="image-crop-wrapper md:col-span-2" data-field="student_photo">
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-1">Unggah Pasfoto Calon Santri (Rasio 3:4)</label>
                            <p class="text-[10px] text-[#0D6B3A]/80 mb-2 font-medium">Format: JPG, PNG, WEBP. Maks. 20MB. Pilih foto, lalu potong (crop) pada modal yang muncul.</p>
                            <input type="file" name="student_photo" id="student_photo" required accept="image/jpeg,image/png,image/jpg,image/webp" class="image-crop-input w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-white file:text-[#0D6B3A] hover:file:bg-gray-100 border-0 p-2.5 rounded-2xl bg-white shadow-sm cursor-pointer" data-aspect-ratio="0.75" data-aspect-label="3:4 (Pasfoto)">
                            
                            <input type="hidden" name="student_photo_crop_x" value="{{ old('student_photo_crop_x') }}">
                            <input type="hidden" name="student_photo_crop_y" value="{{ old('student_photo_crop_y') }}">
                            <input type="hidden" name="student_photo_crop_width" value="{{ old('student_photo_crop_width') }}">
                            <input type="hidden" name="student_photo_crop_height" value="{{ old('student_photo_crop_height') }}">
                            
                            <!-- Hidden input untuk Base64 hasil crop -->
                            <input type="hidden" id="student_photo_base64" name="student_photo_base64" value="{{ old('student_photo_base64') }}">

                            <div class="image-crop-status hidden mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 shadow-inner">
                                <p class="text-xs font-semibold text-emerald-800 mb-2">✓ Pasfoto berhasil di-crop</p>
                                <img src="" alt="Pratinjau Pasfoto" class="image-crop-result-preview max-h-48 rounded-xl border border-emerald-200 object-contain bg-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Nama Lengkap Calon Santri -->
                <div class="bg-[#D5E2DB] rounded-3xl p-6 md:p-8 space-y-6">
                    <h3 class="text-[#0D6B3A] font-bold text-md tracking-wide">Nama Lengkap Calon Santri</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nama Lengkap Calon Santri</label>
                            <input type="text" name="student_name" value="{{ old('student_name') }}" placeholder="Narendra" required class="w-full rounded-2xl bg-[#BACDC3] border-0 p-3.5 text-sm shadow-sm placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Jenis Kelamin</label>
                            <select name="gender" required class="w-full rounded-2xl bg-[#E8F81D] border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" required class="w-full rounded-2xl bg-[#E8F81D] border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Jenjang Pendidikan Dituju</label>
                            <select name="target_class" required class="w-full rounded-2xl bg-[#E8F81D] border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                                <option value="" disabled {{ !old('target_class') && !$program ? 'selected' : '' }}>-- Pilih Jenjang Pendidikan --</option>
                                @foreach($educationPrograms as $programOption)
                                    @php
                                        $isSelected = old('target_class') == $programOption->name || 
                                                      (strtolower($program) == strtolower($programOption->slug));
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
                <div class="bg-[#D5E2DB] rounded-3xl p-6 md:p-8 space-y-6">
                    <h3 class="text-[#0D6B3A] font-bold text-md tracking-wide">Kontak & Alamat Domisili</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Alamat Email Aktif Pendaftar (Gmail)</label>
                            <input type="email" name="student_email" value="{{ old('student_email') }}" placeholder="Contoh: narendra@gmail.com" required class="w-full rounded-2xl bg-white border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nomor WhatsApp Pendaftar (Aktif)</label>
                            <input type="text" name="student_phone" value="{{ old('student_phone') }}" placeholder="Contoh: 081234567890" required class="w-full rounded-2xl bg-white border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Alamat Lengkap Domisili Rumah</label>
                            <textarea name="residential_address" rows="4" placeholder="Tuliskan alamat lengkap domisili Anda di sini..." required class="w-full rounded-2xl bg-white border-0 p-4 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">{{ old('residential_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: Data Orang Tua / Wali -->
                <div class="bg-[#D5E2DB] rounded-3xl p-6 md:p-8 space-y-6">
                    <h3 class="text-[#0D6B3A] font-bold text-md tracking-wide">Data Orang Tua atau Wali</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Hubungan Keluarga Wali</label>
                            <select name="guardian_relationship" required class="w-full rounded-2xl bg-white border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                                <option value="Father" {{ old('guardian_relationship') == 'Father' ? 'selected' : '' }}>Father (Ayah)</option>
                                <option value="Guardian" {{ old('guardian_relationship') == 'Guardian' ? 'selected' : '' }}>Guardian (Wali)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nama Lengkap Ayah atau Wali</label>
                            <input type="text" name="father_name" value="{{ old('father_name') }}" placeholder="Nama Ayah/Wali" required class="w-full rounded-2xl bg-white border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-[#0D6B3A] mb-2">Nama Lengkap Ibu Kandung</label>
                            <input type="text" name="mother_name" value="{{ old('mother_name') }}" placeholder="Nama Ibu Kandung" required class="w-full rounded-2xl bg-white border-0 p-3.5 text-sm shadow-sm focus:ring-2 focus:ring-[#0D6B3A]">
                        </div>
                    </div>
                </div>

                <!-- Submit Button and Disclaimer -->
                <div class="space-y-6 pt-4">
                    <div>
                        <button type="submit" class="inline-flex items-center justify-center px-8 py-4 rounded-full bg-[#E8F81D] hover:bg-yellow-400 text-gray-900 font-bold text-md transition shadow-lg hover:shadow-xl">
                            Kirim Formulir Pendaftaran Sekarang &gt;
                        </button>
                    </div>
                    
                    <p class="text-xs text-gray-500 leading-relaxed max-w-3xl">
                        Dengan mengirimkan formulir ini, Anda menyatakan bahwa seluruh data yang diisi benar dan bersedia mengikuti seluruh prosedur seleksi penerimaan santri baru di Pondok Pesantren Nurul Khidmah secara resmi.
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
                // Tarik data base64 dari preview image src
                if (previewImg && previewImg.src && previewImg.src.startsWith('data:image')) {
                    base64Input.value = previewImg.src;
                }

                // Validasi jika belum di-crop
                if (!base64Input.value) {
                    e.preventDefault();
                    alert('Silakan selesaikan crop Pasfoto Calon Santri terlebih dahulu — pilih gambar, atur area, lalu klik Terapkan crop.');
                    fileInput.focus();
                    return;
                }

                // Bersihkan file input agar browser tidak mengirimkan berkas asli yang berukuran besar ke server PHP
                // Ini mencegah error "failed to upload" (limit 2MB PHP) secara total!
                fileInput.value = '';
            });
        });
    </script>
</body>
</html>
