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
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-800 dark:text-slate-100 font-bold">Data Pribadi Santri</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Nama Lengkap</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->student_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Jenis Kelamin</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Tanggal Lahir</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ \Carbon\Carbon::parse($registration->birth_date)->format('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Tujuan Kelas & Cabang</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->target_class }} - Cabang {{ $registration->branch }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Tahun Ajaran</p>
                    <p class="font-medium text-[#0D6B3A] dark:text-green-400 font-bold">{{ $registration->academic_year }}</p>
                </div>
                <div class="md:col-span-2 mt-2">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Alamat Domisili</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->residential_address }}</p>
                </div>
            </div>
        </div>

        <!-- Data Wali dan Kontak -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-800 dark:text-slate-100 font-bold">Data Wali & Kontak Pendaftar</div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Nama Orang Tua / Wali</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->guardian_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">No. WhatsApp Pendaftar</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->guardian_phone }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Email Pendaftar</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ $registration->guardian_email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-1">Hubungan Keluarga Wali</p>
                    <p class="font-medium text-gray-900 dark:text-slate-100">{{ str_replace('Hubungan Wali: ', '', $registration->education_qualification) }}</p>
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

        <!-- Cetak Bukti Pendaftaran -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden p-6">
            <h3 class="text-sm text-gray-500 dark:text-slate-400 font-semibold mb-3 uppercase tracking-wide">Dokumen Pendaftaran</h3>
            <a href="{{ route('admin.registrations.pdf', $registration->id) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 bg-[#0D6B3A] hover:bg-[#09502b] text-white font-medium py-2.5 px-4 rounded-md transition shadow-sm text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Bukti Pendaftaran
            </a>
            <p class="text-xs text-gray-500 mt-2 text-center">Membuka lembar bukti pendaftaran dengan kop surat pesantren dalam format siap cetak/PDF.</p>
        </div>

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

    </div>
</div>
@endsection
