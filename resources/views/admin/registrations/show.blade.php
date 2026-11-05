@extends('layouts.admin')

@section('title', 'Detail Pendaftar')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center">
        <a href="{{ route('admin.registrations.index') }}" class="text-green-700 dark:text-green-300 hover:text-green-900 dark:hover:text-green-200 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-slate-100">Detail Pendaftar: {{ $registration->registration_number }}</h1>
            <p class="text-gray-600 dark:text-slate-400">Informasi lengkap biodata santri dan wali.</p>
        </div>
    </div>
    
    <!-- Status Badge di Header -->
    <div>
        @if($registration->status == 'pending')
            <span class="px-4 py-2 inline-flex text-sm font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">PENDING</span>
        @elseif($registration->status == 'accepted')
            <span class="px-4 py-2 inline-flex text-sm font-bold rounded-full bg-green-100 text-green-800 border border-green-200">APPROVED</span>
        @else
            <span class="px-4 py-2 inline-flex text-sm font-bold rounded-full bg-red-100 text-red-800 border border-red-200">REJECTED</span>
        @endif
    </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Bagian Kiri: Info Detail -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Data Santri -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-800 dark:text-slate-100 font-bold">1. Data Pribadi Calon Siswa / Santri</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Nama Lengkap</p>
                    <p class="font-bold text-gray-900 dark:text-slate-100 text-base">{{ $registration->student_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">NISN (Nomor Induk Siswa Nasional)</p>
                    <p class="font-mono font-bold text-emerald-700 dark:text-emerald-400 text-base">{{ $registration->nisn ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Jenis Kelamin</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->gender == 'L' ? 'Laki-laki (Putra)' : 'Perempuan (Putri)' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Tempat &amp; Tanggal Lahir</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">
                        {{ $registration->birth_place ? $registration->birth_place . ', ' : '' }}{{ \Carbon\Carbon::parse($registration->birth_date)->format('d F Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Asal Sekolah (SD/MI)</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->previous_school ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Status Asrama</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($registration->boarding_status ?? 'Mukim') === 'Mukim' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' }}">
                        {{ $registration->boarding_status ?? 'Mukim' }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Pilihan Kelas / Program</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->target_class }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Tahun Ajaran</p>
                    <p class="font-medium text-[#0D6B3A] dark:text-green-400 font-bold">{{ $registration->academic_year }}</p>
                </div>
                <div class="md:col-span-2 mt-1 pt-3 border-t border-gray-100 dark:border-slate-800">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Alamat Domisili Siswa</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->residential_address }}</p>
                </div>
            </div>
        </div>

        <!-- Data Wali dan Kontak -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-800 dark:text-slate-100 font-bold">2. Data Orang Tua / Wali Murid</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Nama Ayah Kandung</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->father_name ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Nama Ibu Kandung</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->mother_name ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Pekerjaan Orang Tua / Wali</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->guardian_occupation ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">No. WhatsApp Wali (Aktif)</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->guardian_phone }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Email Wali</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->guardian_email ?? '-' }}</p>
                </div>
                <div class="md:col-span-2 mt-1 pt-3 border-t border-gray-100 dark:border-slate-800">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Alamat Orang Tua / Wali</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->guardian_address ?: $registration->residential_address }}</p>
                </div>
            </div>
        </div>

        <!-- Berkas Dokumen Upload -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-800 dark:text-slate-100 font-bold">3. Berkas Dokumen Persyaratan Digital</div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- KK --}}
                <div class="p-4 rounded-xl border border-gray-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/50 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Kartu Keluarga (KK)</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-slate-100 mt-1">
                            {{ $registration->file_kk ? 'Berkas Telah Diunggah' : 'Belum Diunggah' }}
                        </p>
                    </div>
                    @if($registration->file_kk)
                        <a href="{{ Storage::url($registration->file_kk) }}" target="_blank" class="mt-3 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                            Lihat / Unduh KK ↗
                        </a>
                    @else
                        <span class="mt-3 text-xs text-gray-400 italic">Tidak ada lampiran</span>
                    @endif
                </div>

                {{-- Akta --}}
                <div class="p-4 rounded-xl border border-gray-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/50 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Akta Kelahiran</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-slate-100 mt-1">
                            {{ $registration->file_akta ? 'Berkas Telah Diunggah' : 'Belum Diunggah' }}
                        </p>
                    </div>
                    @if($registration->file_akta)
                        <a href="{{ Storage::url($registration->file_akta) }}" target="_blank" class="mt-3 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                            Lihat / Unduh Akta ↗
                        </a>
                    @else
                        <span class="mt-3 text-xs text-gray-400 italic">Tidak ada lampiran</span>
                    @endif
                </div>

                {{-- KTP --}}
                <div class="p-4 rounded-xl border border-gray-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/50 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase">KTP Orang Tua</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-slate-100 mt-1">
                            {{ $registration->file_ktp ? 'Berkas Telah Diunggah' : 'Belum Diunggah' }}
                        </p>
                    </div>
                    @if($registration->file_ktp)
                        <a href="{{ Storage::url($registration->file_ktp) }}" target="_blank" class="mt-3 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                            Lihat / Unduh KTP ↗
                        </a>
                    @else
                        <span class="mt-3 text-xs text-gray-400 italic">Tidak ada lampiran</span>
                    @endif
                </div>

                {{-- Ijazah / SKL --}}
                <div class="p-4 rounded-xl border border-gray-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/50 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-500 uppercase">Ijazah / SKL</span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-slate-100 mt-1">
                            {{ $registration->file_ijazah ? 'Berkas Telah Diunggah' : 'Belum Diunggah' }}
                        </p>
                    </div>
                    @if($registration->file_ijazah)
                        <a href="{{ Storage::url($registration->file_ijazah) }}" target="_blank" class="mt-3 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                            Lihat / Unduh Ijazah ↗
                        </a>
                    @else
                        <span class="mt-3 text-xs text-gray-400 italic">Tidak ada lampiran</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Bagian Kanan: Foto dan Aksi Status -->
    <div class="space-y-6">
        
        <!-- Foto -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden p-6 text-center">
            <h3 class="text-sm text-gray-500 dark:text-slate-400 font-semibold mb-4 uppercase tracking-wide">Pas Foto Santri</h3>
            @if($registration->student_photo)
                <img src="{{ Storage::url($registration->student_photo) }}" alt="Foto Santri" class="w-40 h-48 object-cover rounded-md border border-gray-300 dark:border-slate-700 mx-auto shadow-sm">
            @else
                <div class="w-40 h-48 bg-gray-100 dark:bg-slate-700 flex items-center justify-center text-gray-400 dark:text-slate-400 border border-dashed border-gray-300 dark:border-slate-700 rounded-md mx-auto">
                    Tidak ada foto
                </div>
            @endif
        </div>

        <!-- Cetak Bukti Pendaftaran & Distribusi Akun Wali -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden p-6">
            <h3 class="text-sm text-gray-500 dark:text-slate-400 font-semibold mb-3 uppercase tracking-wide">Dokumen Pendaftaran & Daftar Ulang</h3>
            <a href="{{ route('admin.registrations.pdf', $registration->id) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 bg-[#0D6B3A] hover:bg-[#09502b] text-white font-medium py-2.5 px-4 rounded-md transition shadow-sm text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                {{ $registration->status === 'accepted' ? 'Cetak Bukti & Akun Wali (PDF)' : 'Cetak Bukti Pendaftaran' }}
            </a>
            <p class="text-xs text-gray-500 mt-2 text-center">Membuka lembar resmi ber-kop surat pesantren (lengkap dengan kartu akun Wali Santri jika sudah diterima).</p>
        </div>

        @if($registration->status === 'accepted' && $registration->santri)
        @php
            $santriLinked = $registration->santri;
            $waliEmail = $santriLinked->user->email ?? $registration->guardian_email;
            $rawPhone = preg_replace('/[^0-9]/', '', (string) $registration->guardian_phone);
            if (str_starts_with($rawPhone, '0')) {
                $waPhone = '62' . substr($rawPhone, 1);
            } elseif (str_starts_with($rawPhone, '8')) {
                $waPhone = '62' . $rawPhone;
            } else {
                $waPhone = $rawPhone;
            }
            $portalUrl = url('/portal-wali');
            $waText = rawurlencode(
                "Assalamu'alaikum Wr. Wb.\n\n" .
                "Bapak/Ibu Wali dari Ananda *{$santriLinked->nama_lengkap}*,\n" .
                "Selamat! Pendaftaran PPDB Pondok Pesantren Nurul Khidmah Lamongan dinyatakan *DITERIMA*.\n\n" .
                "Berikut informasi Nomor Induk Santri (NIS) & Akun Portal Pemantauan Wali Santri:\n" .
                "• Link Portal Wali: {$portalUrl}\n" .
                "• Login via NIS Ananda: *{$santriLinked->nis}*\n" .
                "• Alternatif Email: *{$waliEmail}*\n" .
                "• Password Awal: *wali123*\n" .
                "• Kelas Awal: *" . ($santriLinked->kelas->nama_kelas ?? 'Menunggu Pembagian Kelas') . "*\n\n" .
                "Cukup buka website pesantren, ketuk tombol *Portal Wali*, lalu masukkan NIS Ananda dan Password di atas.\n" .
                "Wassalamu'alaikum Wr. Wb.\n— Tata Usaha Ponpes Nurul Khidmah Lamongan"
            );
        @endphp
        <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-xl shadow-sm border border-emerald-200 dark:border-emerald-800 overflow-hidden p-6">
            <h3 class="text-sm text-emerald-800 dark:text-emerald-300 font-bold mb-3 uppercase tracking-wide">Akun Portal Wali Santri Aktif</h3>
            <div class="space-y-2 text-xs text-gray-700 dark:text-slate-200 mb-4 bg-white dark:bg-slate-900 p-3.5 rounded-lg border border-emerald-200 dark:border-emerald-800/60">
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">NIS Terbit:</span>
                    <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $santriLinked->nis }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Kelas:</span>
                    <span class="font-semibold">{{ $santriLinked->kelas->nama_kelas ?? 'Belum Diplot' }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Email Login:</span>
                    <span class="font-mono font-semibold break-all text-right">{{ $waliEmail }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Password Awal:</span>
                    <span class="font-mono font-bold">wali123</span>
                </div>
            </div>
            <div class="space-y-2">
                @if($waPhone)
                <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" rel="noopener noreferrer"
                   class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-md transition shadow-sm text-xs">
                    Kirim Akun Wali via WhatsApp
                </a>
                @endif
                <a href="{{ route('admin.santri.edit', $santriLinked->id) }}"
                   class="w-full inline-flex items-center justify-center gap-2 bg-white dark:bg-slate-800 hover:bg-gray-50 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 font-semibold py-2 px-4 rounded-md transition text-xs">
                    Lihat / Atur Kelas di Data Master Santri
                </a>
            </div>
        </div>
        @endif

        <!-- Aksi Perubahan Status -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-800 dark:text-slate-100 font-bold">Ubah Status Pendaftaran</div>
            <div class="p-6">
                <form action="{{ route('admin.registrations.update-status', $registration->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-2">Pilih Keputusan:</label>
                    <select name="status" id="status" class="w-full rounded-md border-gray-300 dark:border-slate-700 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 p-2 border bg-white dark:bg-slate-950 text-gray-900 dark:text-slate-100">
                            <option value="pending" {{ $registration->status == 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                            <option value="accepted" {{ $registration->status == 'accepted' ? 'selected' : '' }}>Approve (Diterima)</option>
                            <option value="rejected" {{ $registration->status == 'rejected' ? 'selected' : '' }}>Reject (Ditolak)</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-medium py-2 px-4 rounded-md transition shadow-sm">
                        Simpan Perubahan
                    </button>
                    <p class="text-xs text-gray-500 mt-3 text-center">Perubahan status bersifat aman dan dapat diubah kembali sewaktu-waktu tanpa menghapus data santri.</p>
                </form>
            </div>
        </div>

        <!-- Hapus Pendaftar (Danger Zone) -->
        <div class="bg-red-50 dark:bg-red-950/20 rounded-xl shadow-sm border border-red-200 dark:border-red-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200 dark:border-red-800 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 font-bold">Zona Bahaya (Danger Zone)</div>
            <div class="p-6">
                <p class="text-xs text-red-700 dark:text-red-300 mb-4 leading-relaxed">
                    Tindakan ini tidak dapat dibatalkan. Seluruh data pendaftaran dan pas foto santri ini akan dihapus secara permanen dari server database.
                </p>
                <form action="{{ route('admin.registrations.destroy', $registration->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pendaftar ini secara permanen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition shadow-sm text-sm">
                        Hapus Pendaftar Secara Permanen
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
