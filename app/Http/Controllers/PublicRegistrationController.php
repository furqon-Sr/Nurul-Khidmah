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
            'student_name' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'target_class' => 'required|string|max:255',

            // Kontak & Alamat Pendaftar (Wali Santri)
            'student_email' => 'required|email|max:255',
            'student_phone' => 'required|string|max:30',
            'residential_address' => 'required|string',

            // Data Orang Tua Kandung
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
        ]);

        // Verifikasi backend untuk status pendaftaran program pendidikan
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

        $guardianName = trim($validated['father_name']) . ' (Ayah) & ' . trim($validated['mother_name']) . ' (Ibu)';
        $guardianEmail = strtolower(trim($validated['student_email']));
        $guardianPhone = trim($validated['student_phone']);
        $residentialAddress = trim($validated['residential_address']);

        // Sinkronisasi Tahun Ajaran Aktif dari Pengaturan Situs / SIAKAD
        $activeYear = \App\Models\PageContent::where('section_key', 'active_academic_year')->value('content_text')
            ?: TahunAjaran::getAktif()?->nama_tahun_ajaran
            ?: (date('Y') . '/' . (date('Y') + 1));

        SantriRegistration::create([
            'registration_number' => $validated['registration_number'],
            'student_name' => $validated['student_name'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'target_class' => $validated['target_class'],
            'academic_year' => $activeYear,
            'branch' => 'Lamongan',
            'guardian_name' => $guardianName,
            'guardian_email' => $guardianEmail,
            'guardian_phone' => $guardianPhone,
            'education_qualification' => 'Orang Tua Kandung',
            'residential_address' => $residentialAddress,
            'student_photo' => $photoPath,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Formulir pendaftaran berhasil dikirim dengan Nomor Registrasi ' . $validated['registration_number'] . '. Silakan tunggu verifikasi dari Panitia PSB Pondok Pesantren Nurul Khidmah Lamongan.');
    }
}
