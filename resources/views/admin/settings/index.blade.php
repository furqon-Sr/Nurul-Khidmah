@extends('layouts.admin')

@section('title', 'Pengaturan Situs')

@section('content')
<div class="mb-6 flex justify-between items-start">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Pengaturan Situs</h1>
        <p class="text-gray-800 dark:text-gray-200">Kelola konten dinamis website dari sini.</p>
    </div>
    <a href="{{ route('admin.flush-cache') }}" onclick="return confirm('Bersihkan cache? Halaman frontend akan memuat data terbaru dari database.')" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 border border-orange-300 dark:bg-orange-900 dark:text-orange-300 dark:border-orange-600 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Bersihkan Cache
    </a>
</div>

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-slate-800 dark:text-slate-200">
    <div class="p-6 bg-white border-b border-gray-200 dark:bg-slate-900 dark:border-slate-700">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 dark:bg-green-900 dark:border-green-700 dark:text-green-200 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 dark:bg-red-900 dark:border-red-700 dark:text-red-200 px-4 py-3 rounded relative">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div x-data="{ activeTab: '{{ request('tab', 'identitas') }}' }">
                        <!-- Tabs Navigation -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button @click="activeTab = 'identitas'" 
                                    :class="activeTab === 'identitas' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-600'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Identitas Utama
                                </button>
                                <button @click="activeTab = 'hero'" 
                                    :class="activeTab === 'hero' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-600'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Hero Section
                                </button>
                                <button @click="activeTab = 'konten'" 
                                    :class="activeTab === 'konten' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-600'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Konten Halaman
                                </button>
                                <button @click="activeTab = 'login_staf'" 
                                    :class="activeTab === 'login_staf' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-600'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm inline-flex items-center gap-1.5">
                                    <span>🔐</span>
                                    <span>Login Staf</span>
                                </button>
                            </nav>
                        </div>

                        <!-- Tab 1: Identitas Utama -->
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" x-show="activeTab === 'identitas'">
                            @csrf
                            <input type="hidden" name="form_type" value="identitas">
                            
                            <div class="space-y-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-800 dark:text-gray-200">Identitas Utama & Footer</h3>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Nama Situs / Yayasan</label>
                                        <input type="text" name="site_name" value="{{ old('site_name', $contents['site_name']->content_text ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Tahun Ajaran Aktif (Untuk Pendaftaran Baru)</label>
                                        <input type="text" name="active_academic_year" value="{{ old('active_academic_year', $contents['active_academic_year']->content_text ?? (date('Y') . '/' . (date('Y') + 1))) }}" placeholder="Contoh: 2026/2027" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">
                                        <p class="text-xs text-gray-500 mt-1">Setiap pendaftaran baru yang dikirimkan oleh calon santri akan otomatis ditandai dengan tahun ajaran aktif ini.</p>
                                    </div>

                                    @include('partials.image-crop-field', [
                                        'name' => 'logo',
                                        'label' => 'Logo Website',
                                        'aspectRatio' => null,
                                        'aspectLabel' => 'Bebas (sesuaikan proporsi logo)',
                                        'currentImageUrl' => isset($contents['logo']) && $contents['logo']->image_path ? asset('storage/' . $contents['logo']->image_path) : null,
                                    ])

                                    @include('partials.image-crop-field', [
                                        'name' => 'favicon',
                                        'label' => 'Ikon Situs (Favicon)',
                                        'aspectRatio' => 1,
                                        'aspectLabel' => '1:1 (ikon persegi)',
                                        'accept' => '.png,.jpg,.jpeg,.svg,.ico,image/png,image/jpeg,image/svg+xml,image/x-icon',
                                        'hint' => 'PNG/JPG: crop wajib di modal. File SVG/ICO disimpan apa adanya tanpa crop.',
                                        'currentImageUrl' => isset($contents['favicon']) && $contents['favicon']->image_path ? asset('storage/' . $contents['favicon']->image_path) : null,
                                    ])

                                    <div>
                                        <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Nomor Telepon Kontak</label>
                                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $contents['contact_phone']->content_text ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Email Kontak</label>
                                        <input type="email" name="contact_email" value="{{ old('contact_email', $contents['contact_email']->content_text ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Alamat Lengkap</label>
                                        <textarea name="contact_address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">{{ old('contact_address', $contents['contact_address']->content_text ?? '') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Teks Pendek Footer (Tentang Yayasan)</label>
                                        <textarea name="footer_text" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">{{ old('footer_text', $contents['footer_text']->content_text ?? '') }}</textarea>
                                    </div>

                                    <div>
                                         <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">
                                             📄 Upload Brosur / Biaya Pendidikan (PDF atau Gambar)
                                         </label>
                                         <p class="text-xs text-gray-800 dark:text-gray-200 mt-1 mb-2">File PDF atau Gambar ini akan langsung diunduh saat pengunjung menekan tombol <strong>"Biaya Pendidikan"</strong> di halaman utama (Hero).</p>
                                         @if(isset($contents['education_fee_pdf']) && $contents['education_fee_pdf']?->image_path)
                                             <div class="mt-2 mb-3 flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                                 <svg class="w-6 h-6 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                                                 <div>
                                                     <p class="text-sm font-semibold text-green-700">✅ Berkas Brosur Aktif Terpasang</p>
                                                     <a href="{{ route('education.download-fee') }}" target="_blank" class="text-xs text-green-600 hover:underline">Klik untuk preview / download file →</a>
                                                 </div>
                                             </div>
                                         @else
                                             <div class="mt-2 mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                 <p class="text-xs text-gray-800 dark:text-gray-200">⚠️ Belum ada berkas brosur. Tombol "Biaya Pendidikan" di hero akan ditampilkan dalam keadaan tidak aktif.</p>
                                             </div>
                                         @endif
                                         <input type="file" name="education_fee_pdf" accept="application/pdf,image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:file:text-slate-200 dark:hover:file:bg-green-600">
                                         <p class="text-xs text-red-600 dark:text-red-400 mt-1">Format: PDF atau Gambar (JPG, JPEG, PNG, WEBP). Maksimal 2MB (Batasan sistem server PHP).</p>
                                    </div>

                                    <div class="border-t border-gray-150 pt-5">
                                        <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">
                                            🖼️ Upload Kop Surat Pesantren (Gambar Latar Belakang PDF)
                                        </label>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 mb-2">Gambar ini akan digunakan sebagai background template saat admin mencetak atau mengekspor Bukti Pendaftaran Santri.</p>
                                        @if(isset($contents['kop_surat']) && $contents['kop_surat']?->image_path)
                                            <div class="mt-2 mb-3 flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg dark:bg-green-950/20 dark:border-green-800">
                                                <svg class="w-6 h-6 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <div>
                                                    <p class="text-sm font-semibold text-green-700 dark:text-green-300">✅ Kop Surat kustom aktif terpasang</p>
                                                    <a href="{{ asset('storage/' . $contents['kop_surat']->image_path) }}" target="_blank" class="text-xs text-green-600 hover:underline dark:text-green-400">Klik untuk preview gambar →</a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-2 mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg dark:bg-yellow-950/20 dark:border-yellow-800">
                                                <p class="text-xs text-gray-600 dark:text-gray-400">⚠️ Menggunakan Kop Surat bawaan: <code class="bg-gray-100 dark:bg-slate-800 px-1 py-0.5 rounded font-mono">public/images/kop surat ppnk.png</code>.</p>
                                            </div>
                                        @endif
                                        <input type="file" name="kop_surat" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:file:text-slate-200 dark:hover:file:bg-green-600">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format: JPG, JPEG, PNG, WEBP. Maksimal 20MB. Digunakan otomatis pada Bukti Pendaftaran, Rapor Santri, Rekap Kelas, dan Laporan Eksekutif Pimpinan.</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 dark:border-slate-700 pt-5">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Pejabat Penandatangan Rapor (Kepala Madrasah)</label>
                                            <input type="text" name="kepala_madrasah_name" value="{{ old('kepala_madrasah_name', $contents['kepala_madrasah_name']->content_text ?? 'Ibu Uswatun Hasanah SP, S.Pd.I.') }}" placeholder="Contoh: Ibu Uswatun Hasanah SP, S.Pd.I." class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">
                                            <p class="text-xs text-gray-500 mt-1">Tercetak otomatis pada kolom pengesahan Rapor Hasil Belajar &amp; Rekap Akademik Kelas.</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Pengasuh / Pimpinan Pondok Pesantren</label>
                                            <input type="text" name="pimpinan_pondok_name" value="{{ old('pimpinan_pondok_name', $contents['pimpinan_pondok_name']->content_text ?? 'KH. Imam Machmudi') }}" placeholder="Contoh: KH. Imam Machmudi" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">
                                            <p class="text-xs text-gray-500 mt-1">Tercetak otomatis pada lembar pengesahan Laporan Eksekutif Track Record Pesantren.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8 border-t border-gray-200 pt-5">
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-[#0D6B3A] text-white px-6 py-2 rounded-md hover:bg-green-800 transition shadow dark:bg-[#0D6B3A] dark:hover:bg-green-700">
                                        Simpan Identitas & Footer
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Tab 2: Hero Section -->
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" x-show="activeTab === 'hero'" x-cloak>
                            @csrf
                            <input type="hidden" name="form_type" value="hero">
                            
                            <div class="space-y-8">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-slate-200">Hero Section (3 Slide)</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    @for($i = 1; $i <= 3; $i++)
                                        @php $hero = $heroes->where('position', $i)->first(); @endphp
                                        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-slate-800 dark:border-slate-700">
                                            <h4 class="font-bold mb-4 text-green-800">Slide {{ $i }}</h4>
                                            
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-200">Judul Slide</label>
                                                    <input type="text" name="hero_{{ $i }}_title" value="{{ old('hero_'.$i.'_title', $hero->title ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100">
                                                </div>

                                                @include('partials.image-crop-field', [
                                                    'name' => 'hero_'.$i.'_image',
                                                    'label' => 'Gambar Banner',
                                                    'aspectRatio' => 16/9,
                                                    'aspectLabel' => '16:9 (hero slide)',
                                                    'currentImageUrl' => ($hero && $hero->image_path) ? asset('storage/' . $hero->image_path) : null,
                                                ])
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <div class="mt-8 border-t border-gray-200 pt-5">
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-[#0D6B3A] text-white px-6 py-2 rounded-md hover:bg-green-800 transition shadow">
                                        Simpan Hero Section
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Tab 3: Konten Halaman — Modular Card UI -->
                        <div x-show="activeTab === 'konten'" x-cloak class="space-y-2">

                            <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700 flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                <span>Setiap bagian disimpan secara <strong>independen</strong>. Klik tombol simpan di tiap card — tidak perlu scroll ke bawah.</span>
                            </div>

                            {{-- CARD 1: Sejarah Pesantren --}}
                            @include('admin.settings.partials.content-card', [
                                'cardId'      => 'sejarah',
                                'formType'    => 'konten_sejarah',
                                'icon'        => '📖',
                                'title'       => 'Sejarah Pesantren',
                                'description' => 'Tampil di halaman Tentang Kami — bagian atas',
                                'fields'      => [
                                    ['key' => 'about_title',       'label' => 'Judul Sejarah',      'type' => 'text',
                                     'hint' => 'Contoh: "Sejarah Singkat Pondok Pesantren Nurul Khidmah"'],
                                    ['key' => 'about_text_1',      'label' => 'Paragraf Pembuka',   'type' => 'rte'],
                                    ['key' => 'about_text_2',      'label' => 'Paragraf Kedua',     'type' => 'rte'],
                                    ['key' => 'about_history_text','label' => 'Narasi Lengkap Sejarah', 'type' => 'rte'],
                                    ['key' => 'about_image',       'label' => 'Foto Gedung / Suasana Pesantren', 'type' => 'image'],
                                ],
                                'contents'    => $contents,
                            ])

                            {{-- CARD 2: Profil Pengasuh --}}
                            @include('admin.settings.partials.content-card', [
                                'cardId'      => 'kyai',
                                'formType'    => 'konten_kyai',
                                'icon'        => '👤',
                                'title'       => 'Profil Pengasuh',
                                'description' => 'Tampil di halaman Tentang Kami — bagian Profil Kyai',
                                'fields'      => [
                                    ['key' => 'about_kyai_name',   'label' => 'Nama Lengkap Pengasuh', 'type' => 'text',
                                     'hint' => 'Contoh: KH. Ahmad Marzuki, Lc.'],
                                    ['key' => 'about_kyai_text_1', 'label' => 'Biografi Singkat',      'type' => 'rte'],
                                    ['key' => 'about_kyai_text_2', 'label' => 'Visi & Pesan Pengasuh', 'type' => 'rte'],
                                    ['key' => 'about_kyai_image',  'label' => 'Foto Pengasuh',         'type' => 'image'],
                                ],
                                'contents'    => $contents,
                            ])


                        </div>
                        {{-- /Tab 3 --}}

                        <!-- Tab 4: Halaman Login Staf -->
                        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" x-show="activeTab === 'login_staf'" x-cloak>
                            @csrf
                            <input type="hidden" name="form_type" value="login_staf">

                            <div class="space-y-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-slate-800">
                                    <div>
                                        <h3 class="text-lg leading-6 font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                            <span>🖼️</span> Visual &amp; Konten Halaman Login Staf
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                            Kelola gambar banner visual di sisi kanan dan teks pada formulir login portal staf (<code class="bg-gray-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-xs text-green-700 font-mono">/portal-staf</code>).
                                        </p>
                                    </div>
                                    <a href="{{ route('staff.login') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#0D6B3A] hover:underline bg-green-50 hover:bg-green-100 dark:bg-green-950/40 px-3.5 py-2 rounded-xl transition">
                                        <span>Buka Halaman Login Staf ↗</span>
                                    </a>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                                    {{-- Kolom Pengaturan (Kiri) --}}
                                    <div class="lg:col-span-7 space-y-6">
                                        {{-- Field Upload Banner Image --}}
                                        <div class="bg-slate-50 dark:bg-slate-900/40 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-4">
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">
                                                    Gambar Banner Visual (Sisi Kanan)
                                                </h4>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    Unggah gambar foto gedung pesantren, kegiatan belajar, atau grafis kustom untuk menggantikan tampilan ilustrasi bawaan.
                                                </p>
                                            </div>

                                            @include('partials.image-crop-field', [
                                                'name' => 'staff_login_image',
                                                'label' => 'Pilih Gambar Banner Baru',
                                                'aspectRatio' => null,
                                                'aspectLabel' => 'Bebas / Vertikal (Rekomendasi rasio potret 4:5 atau 3:4)',
                                                'hint' => 'Format: JPG, JPEG, PNG, WEBP. Maksimal 20MB. Anda dapat memotong area gambar pada modal cropper.',
                                                'currentImageUrl' => isset($contents['staff_login_image']) && $contents['staff_login_image']->image_path ? asset('storage/' . $contents['staff_login_image']->image_path) : null,
                                            ])

                                            @if(isset($contents['staff_login_image']) && $contents['staff_login_image']->image_path)
                                                <div class="mt-4 p-3.5 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 rounded-xl flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Banner kustom aktif terpasang</span>
                                                    </div>
                                                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-rose-700 dark:text-rose-400 cursor-pointer hover:underline">
                                                        <input type="checkbox" name="remove_staff_login_image" value="1" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                                        <span>Hapus gambar (kembali ke grafis bawaan)</span>
                                                    </label>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Teks Sambutan & Subjudul --}}
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                                                    Judul Sambutan
                                                </label>
                                                <input type="text" name="staff_login_title" value="{{ old('staff_login_title', $contents['staff_login_title']->content_text ?? 'Welcome Back') }}" placeholder="Welcome Back" class="w-full rounded-xl border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100 text-sm">
                                                <p class="text-xs text-gray-500 mt-1">Ditampilkan pada judul formulir login (ikon lambaian 👋 disematkan otomatis).</p>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                                                    Subjudul / Tagline Singkat
                                                </label>
                                                <input type="text" name="staff_login_subtitle" value="{{ old('staff_login_subtitle', $contents['staff_login_subtitle']->content_text ?? 'Nurul Khidmah – Where Every Event Begins With Insight.') }}" placeholder="Contoh: Nurul Khidmah – Where Every Event Begins With Insight." class="w-full rounded-xl border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-500 text-gray-900 dark:text-slate-100 text-sm">
                                                <p class="text-xs text-gray-500 mt-1">Ditampilkan di bawah judul sambutan.</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Kolom Pratinjau (Kanan) --}}
                                    <div class="lg:col-span-5">
                                        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl p-4 bg-slate-50/60 dark:bg-slate-900/60 space-y-3">
                                            <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-400">
                                                <span>Pratinjau Banner Sisi Kanan</span>
                                                <span class="text-[11px] font-normal text-slate-400">Sesuai Referensi</span>
                                            </div>

                                            <div class="w-full h-80 rounded-2xl overflow-hidden relative shadow-md flex items-center justify-center border border-slate-200/80 bg-slate-100">
                                                @if(isset($contents['staff_login_image']) && $contents['staff_login_image']->image_path)
                                                    <img src="{{ asset('storage/' . $contents['staff_login_image']->image_path) }}" alt="Pratinjau Banner" class="w-full h-full object-cover">
                                                @else
                                                    {{-- Default reference art preview --}}
                                                    <div class="w-full h-full relative overflow-hidden bg-gradient-to-b from-[#EBF5F0] via-[#E2EFE9] to-[#CDE4DA] flex items-center justify-center p-4">
                                                        {{-- Top right orb --}}
                                                        <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full bg-gradient-to-br from-[#045F33] via-[#02522c] to-[#004524] opacity-90 shadow-xl"></div>
                                                        <div class="absolute top-3 right-10 w-1.5 h-1.5 rounded-full bg-[#004524]"></div>
                                                        <div class="absolute top-9 right-14 w-2 h-2 rounded-full bg-[#045F33]"></div>

                                                        {{-- Bottom left orb --}}
                                                        <div class="absolute -bottom-10 -left-10 w-28 h-28 rounded-full bg-gradient-to-tr from-[#004524] via-[#02522c] to-[#045F33] opacity-90 shadow-xl"></div>
                                                        <div class="absolute bottom-6 left-16 w-1.5 h-1.5 rounded-full bg-[#004524]"></div>

                                                        {{-- Center emblem & title --}}
                                                        <div class="relative z-10 flex items-center gap-3">
                                                            <div class="w-14 h-14 rounded-full bg-white shadow-lg flex items-center justify-center p-2 border border-white">
                                                                @if(isset($contents['logo']) && $contents['logo']->image_path)
                                                                    <img src="{{ asset('storage/' . $contents['logo']->image_path) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                                                                @else
                                                                    <span class="text-xs font-black text-slate-800">NK</span>
                                                                @endif
                                                            </div>
                                                            <span class="text-xl font-black text-slate-900 tracking-tight">
                                                                {{ $contents['site_name']->content_text ?? 'Eventure' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-center">
                                                Jika gambar kustom belum diunggah, grafis latar default (warna perak-kebiruan, orbs 3D, emblem putih) akan ditampilkan otomatis.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 border-t border-gray-200 dark:border-slate-700 pt-5 flex justify-end">
                                    <button type="submit" class="bg-[#0D6B3A] text-white px-6 py-2.5 rounded-xl hover:bg-green-800 transition shadow font-medium text-sm flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Simpan Pengaturan Login Staf</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>{{-- /x-data tabs --}}

                </div>
    </div>
</div>

{{-- ============================================================
     Quill Rich Text Editor — CDN (loaded once for the whole page)
     ============================================================ --}}
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<script>
// ─── Quill RTE Init & Fallback ────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const quillEls = document.querySelectorAll('.quill-editor-container');

    if (typeof Quill === 'undefined') {
        console.warn('Quill library not loaded – falling back to native textarea.');
        quillEls.forEach(fallbackToTextarea);
        return;
    }

    quillEls.forEach(function (el) {
        if (el.dataset.quillInitialized || el.classList.contains('ql-container')) {
            return;
        }
        el.dataset.quillInitialized = 'true';

        const rawContent = el.dataset.content || '';
        const hiddenId   = el.id.replace('quill-', 'hidden-');
        const hiddenInput = document.getElementById(hiddenId);

        try {
            const quill = new Quill(el, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['clean'],
                    ],
                },
            });

            if (rawContent) {
                quill.clipboard.dangerouslyPasteHTML(rawContent);
            }

            el.closest('form').addEventListener('submit', function () {
                const activeHiddenInput = document.getElementById(hiddenId);
                if (activeHiddenInput && document.body.contains(el)) {
                    activeHiddenInput.value = quill.root.innerHTML;
                }
            });
        } catch (e) {
            console.error('Quill init error on', el.id, e);
            fallbackToTextarea(el);
        }
    });

    function fallbackToTextarea(el) {
        const parent = el.parentNode;
        if (!parent) return;

        const initialHtml = el.dataset.content || '';
        
        const textarea = document.createElement('textarea');
        textarea.className = 'w-full rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-950 shadow-sm focus:border-green-500 focus:ring-green-400 text-sm text-gray-900 dark:text-slate-100 p-3';
        textarea.style.minHeight = '140px';
        textarea.rows = 6;
        
        const hiddenId = el.id.replace('quill-', 'hidden-');
        const hiddenInput = document.getElementById(hiddenId);
        if (hiddenInput) {
            textarea.name = hiddenInput.name;
            hiddenInput.remove();
        } else {
            textarea.name = el.id.substring(el.id.lastIndexOf('-') + 1);
        }
        
        textarea.value = initialHtml;

        const nextSibling = el.nextSibling;
        el.remove();

        if (nextSibling) {
            parent.insertBefore(textarea, nextSibling);
        } else {
            parent.appendChild(textarea);
        }
    }
});

// ─── Image Live Preview ───────────────────────────────────────
function previewImage(input, key) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            let img = document.getElementById('preview-' + key);
            if (img) {
                img.src = e.target.result;
            } else {
                const placeholder = document.getElementById('preview-placeholder-' + key);
                if (placeholder) {
                    const newImg = document.createElement('img');
                    newImg.id        = 'preview-' + key;
                    newImg.src       = e.target.result;
                    newImg.className = 'w-32 h-32 object-cover rounded-xl border-2 border-green-200 shadow-sm';
                    placeholder.replaceWith(newImg);
                }
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ─── Card Collapse Toggle ─────────────────────────────────────
function toggleCard(cardId) {
    const body    = document.getElementById('body-' + cardId);
    const iconDn  = document.getElementById('icon-down-' + cardId);
    const iconUp  = document.getElementById('icon-up-' + cardId);
    const isHidden = body.style.display === 'none';
    body.style.display   = isHidden ? '' : 'none';
    iconDn.classList.toggle('hidden', !isHidden);
    iconUp.classList.toggle('hidden',  isHidden);
}
</script>

@endsection
