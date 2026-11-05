<?php

namespace App\Http\Controllers;

use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use App\Services\ImageCropService;
use Illuminate\Http\Request;

class PublicRegistrationController extends Controller
{
    public function __construct(
        protected ImageCropService $imageCrop
    ) {}

    /**
     * Tampilkan formulir pendaftaran santri baru Pondok Pesantren Nurul Khidmah Lamongan
     */
    public function create($program = null)
    {
        do {
            $registrationNumber = (string) rand(100000, 999999);
        } while (SantriRegistration::where('registration_number', $registrationNumber)->exists());

        $educationPrograms = \App\Models\EducationProgram::active()->orderBy('order')->get();

        return view('registration', compact('registrationNumber', 'program', 'educationPrograms'));
    }

    /**
     * Simpan data pendaftaran PPDB (Fokus objek: Pondok Pesantren Nurul Khidmah Lamongan)
     */
    public function store(Request $request)
    {
        if ($request->filled('website_url')) {
            abort(422, 'Permintaan tidak valid.');
        }

        $validated = $request->validate([
            'registration_number' => 'required|string|max:255|unique:santri_registrations,registration_number',
            'student_photo_base64' => 'required|string',

            // Bagian 1: Data Siswa
            'student_name' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'required|date',
            'residential_address' => 'required|string',
            'previous_school' => 'nullable|string|max:255',
            'nisn' => 'nullable|string|max:20',
            'boarding_status' => 'nullable|in:Mukim,Tidak Mukim',
            'target_class' => 'required|string|max:255',

            // Bagian 2: Data Orang Tua / Wali
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'guardian_occupation' => 'nullable|string|max:255',
            'guardian_address' => 'nullable|string',
            'student_email' => 'nullable|email|max:255',
            'student_phone' => 'required|string|max:30',

            // Bagian 3: Dokumen Unggahan (PDF / Gambar maks 3MB)
            'file_kk' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
            'file_akta' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
            'file_ktp' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
            'file_ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:3072',
        ]);

        // Verifikasi backend: hanya program pendidikan yang aktif dan dibuka pendaftarannya oleh admin
        $programExists = \App\Models\EducationProgram::where('name', $validated['target_class'])
            ->where('is_active', true)
            ->where('is_registration_open', true)
            ->exists();

        if (! $programExists) {
            return back()->withErrors(['target_class' => 'Pendaftaran untuk program pendidikan yang Anda pilih saat ini sedang ditutup.'])->withInput();
        }

        // Proses pasfoto 3:4 menggunakan ImageCropService (PHP GD Extension)
        try {
            $photoPath = $this->imageCrop->storeBase64Cropped($validated['student_photo_base64'], 'photos');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['student_photo' => $e->getMessage()])->withInput();
        }

        // Upload Berkas Dokumen Pendukung (KK, Akta, KTP, Ijazah)
        $fileKk = $request->hasFile('file_kk') ? $request->file('file_kk')->store('documents/kk', 'public') : null;
        $fileAkta = $request->hasFile('file_akta') ? $request->file('file_akta')->store('documents/akta', 'public') : null;
        $fileKtp = $request->hasFile('file_ktp') ? $request->file('file_ktp')->store('documents/ktp', 'public') : null;
        $fileIjazah = $request->hasFile('file_ijazah') ? $request->file('file_ijazah')->store('documents/ijazah', 'public') : null;

        $fatherName = trim($validated['father_name']);
        $motherName = trim($validated['mother_name']);
        $guardianName = $fatherName . ' (Ayah) & ' . $motherName . ' (Ibu)';
        $guardianEmail = strtolower(trim($validated['student_email'] ?? ''));
        $guardianPhone = trim($validated['student_phone']);
        $residentialAddress = trim($validated['residential_address']);

        // Sinkronisasi Tahun Ajaran Aktif dari Pengaturan Situs / SIAKAD
        $activeYear = \App\Models\PageContent::where('section_key', 'active_academic_year')->value('content_text')
            ?: TahunAjaran::getAktif()?->nama_tahun_ajaran
            ?: (date('Y') . '/' . (date('Y') + 1));

        SantriRegistration::create([
            'registration_number' => $validated['registration_number'],
            'student_name' => $validated['student_name'],
            'birth_place' => $validated['birth_place'] ?? null,
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'residential_address' => $residentialAddress,
            'previous_school' => $validated['previous_school'] ?? null,
            'nisn' => $validated['nisn'] ?? null,
            'boarding_status' => $validated['boarding_status'] ?? 'Mukim',
            'target_class' => $validated['target_class'],
            'academic_year' => $activeYear,
            'branch' => 'Lamongan',
            'guardian_name' => $guardianName,
            'father_name' => $fatherName,
            'mother_name' => $motherName,
            'guardian_occupation' => $validated['guardian_occupation'] ?? null,
            'guardian_address' => $validated['guardian_address'] ?? $residentialAddress,
            'guardian_email' => $guardianEmail ?: null,
            'guardian_phone' => $guardianPhone,
            'education_qualification' => 'Orang Tua Kandung',
            'student_photo' => $photoPath,
            'file_kk' => $fileKk,
            'file_akta' => $fileAkta,
            'file_ktp' => $fileKtp,
            'file_ijazah' => $fileIjazah,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Formulir pendaftaran berhasil dikirim dengan Nomor Registrasi ' . $validated['registration_number'] . '. Silakan tunggu verifikasi dari Panitia PSB Pondok Pesantren Nurul Khidmah Lamongan.');
    }
}
