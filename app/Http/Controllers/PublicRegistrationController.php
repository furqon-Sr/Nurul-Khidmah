<?php

namespace App\Http\Controllers;

use App\Models\SantriRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicRegistrationController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran
     */
    public function create($program = null)
    {
        // Generate nomor virtual pendaftaran unik 6 digit
        do {
            $registrationNumber = rand(100000, 999999);
        } while (SantriRegistration::where('registration_number', $registrationNumber)->exists());

        // Ambil semua program pendidikan aktif untuk dropdown
        $educationPrograms = \App\Models\EducationProgram::active()->orderBy('order')->get();

        return view('registration', compact('registrationNumber', 'program', 'educationPrograms'));
    }

    /**
     * Simpan data pendaftaran
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255|unique:santri_registrations,registration_number',
            'student_photo_base64' => 'required|string',
            'student_name' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'target_class' => 'required|string|max:255',

            // Kontak & Alamat Pendaftar
            'student_email' => 'required|email|max:255',
            'student_phone' => 'required|string|max:255',
            'residential_address' => 'required|string',

            // Orang Tua / Wali
            'guardian_relationship' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
        ]);

        // Verifikasi backend untuk status pendaftaran program pendidikan
        $programExists = \App\Models\EducationProgram::where('name', $validated['target_class'])
            ->where('is_active', true)
            ->where('is_registration_open', true)
            ->exists();

        if (!$programExists) {
            return back()->withErrors(['target_class' => 'Pendaftaran untuk program pendidikan yang Anda pilih saat ini sedang ditutup.'])->withInput();
        }

        // Proses Base64 pasfoto hasil crop
        $photoPath = null;
        if ($request->filled('student_photo_base64')) {
            $base64Data = $request->input('student_photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, jpeg, webp

                if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    return back()->withErrors(['student_photo' => 'Format pasfoto tidak valid. Harap unggah berkas gambar.'])->withInput();
                }

                $data = base64_decode($data);
                if ($data === false) {
                    return back()->withErrors(['student_photo' => 'Gagal memproses pasfoto.'])->withInput();
                }

                $filename = \Illuminate\Support\Str::uuid() . '.' . ($type === 'jpeg' ? 'jpg' : $type);
                $photoPath = 'photos/' . $filename;

                if (!Storage::disk('public')->exists('photos')) {
                    Storage::disk('public')->makeDirectory('photos');
                }

                Storage::disk('public')->put($photoPath, $data);
            } else {
                return back()->withErrors(['student_photo' => 'Pasfoto tidak valid.'])->withInput();
            }
        }

        // Gabungkan data Ayah dan Ibu untuk nama wali
        $guardianName = $validated['father_name'] . ' (Ayah) & ' . $validated['mother_name'] . ' (Ibu)';

        // Map fields directly to database columns
        $guardianEmail = $validated['student_email'];
        $guardianPhone = $validated['student_phone'];
        $residentialAddress = $validated['residential_address'];
        $educationQualification = 'Hubungan Wali: ' . $validated['guardian_relationship'];

        // Ambil tahun ajaran aktif dari pengaturan situs (default ke tahun sekarang/tahun depan jika belum di-set)
        $activeYear = \App\Models\PageContent::where('section_key', 'active_academic_year')->value('content_text')
            ?? (date('Y') . '/' . (date('Y') + 1));

        // Buat data pendaftaran baru
        SantriRegistration::create([
            'registration_number' => $validated['registration_number'],
            'student_name' => $validated['student_name'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'target_class' => $validated['target_class'],
            'academic_year' => $activeYear,
            'branch' => $validated['branch'],
            'guardian_name' => $guardianName,
            'guardian_email' => $guardianEmail,
            'guardian_phone' => $guardianPhone,
            'education_qualification' => $educationQualification,
            'residential_address' => $residentialAddress,
            'student_photo' => $photoPath,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Formulir pendaftaran berhasil dikirim. Silakan tunggu konfirmasi dari pihak pondok pesantren.');
    }
}
