<?php

namespace App\Http\Controllers;

use App\Models\SantriRegistration;
use Illuminate\Http\Request;

class SantriRegistrationController extends Controller
{
    /**
     * Menampilkan daftar semua pendaftar dengan fitur pencarian dan filter
     */
    public function index(Request $request)
    {
        $query = SantriRegistration::query();

        // Ambil tahun ajaran aktif dari database (default ke tahun sekarang/depan)
        $activeYear = \App\Models\PageContent::where('section_key', 'active_academic_year')->value('content_text')
            ?? (date('Y') . '/' . (date('Y') + 1));

        // Ambil semua daftar unik tahun ajaran dari database untuk filter
        $academicYears = SantriRegistration::distinct()->pluck('academic_year')->filter()->toArray();
        if (!in_array($activeYear, $academicYears)) {
            $academicYears[] = $activeYear;
        }
        sort($academicYears);

        // Pilih tahun ajaran filter (default ke tahun aktif saat ini)
        $selectedYear = $request->input('academic_year', $activeYear);

        if ($selectedYear !== 'all') {
            $query->where('academic_year', $selectedYear);
        }

        // Pencarian berdasarkan nama, nomor registrasi, atau nama wali
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('guardian_name', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan kelas/jenjang pendidikan
        if ($request->filled('target_class')) {
            $query->where('target_class', $request->input('target_class'));
        }

        // Filter berdasarkan status pendaftaran
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter berdasarkan cabang pesantren
        if ($request->filled('branch')) {
            $query->where('branch', $request->input('branch'));
        }

        // Ekspor ke file Excel (CSV terpisah semicolon dengan UTF-8 BOM & sep=;) jika parameter action = export
        if ($request->input('action') === 'export') {
            return $this->exportExcel($query->latest()->get());
        }

        // Mengambil data pendaftar, diurutkan dari yang terbaru dengan pagination
        $registrations = $query->latest()->paginate(15)->withQueryString();

        // Mengambil daftar program pendidikan aktif dari database untuk opsi filter
        $classes = \App\Models\EducationProgram::active()->orderBy('order')->pluck('name');
        $branches = ['Surabaya', 'Lamongan'];

        // Menghitung statistik pendaftaran untuk dasbor ringkasan berdasarkan filter tahun ajaran yang dipilih
        $statsQuery = SantriRegistration::query();
        if ($selectedYear !== 'all') {
            $statsQuery->where('academic_year', $selectedYear);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'accepted' => (clone $statsQuery)->where('status', 'accepted')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        return view('admin.registrations.index', compact('registrations', 'classes', 'branches', 'stats', 'academicYears', 'selectedYear', 'activeYear'));
    }

    /**
     * Ekspor data pendaftar ke file CSV kompatibel penuh dengan Excel
     */
    protected function exportExcel($registrations)
    {
        $filename = 'Data_Pendaftar_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Sinyalkan separator titik koma (;) ke Excel secara native
            fwrite($file, "sep=;\n");

            // Header kolom Excel
            fputcsv($file, [
                'No. Registrasi',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Tanggal Lahir',
                'Tujuan Kelas / Jenjang',
                'Tahun Ajaran',
                'Cabang Pesantren',
                'Nama Orang Tua / Wali',
                'Email Pendaftar',
                'No. WhatsApp Pendaftar',
                'Hubungan Keluarga Wali',
                'Alamat Domisili',
                'Tanggal Pendaftaran',
                'Status'
            ], ';');

            foreach ($registrations as $reg) {
                fputcsv($file, [
                    $reg->registration_number,
                    $reg->student_name,
                    $reg->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                    $reg->birth_date,
                    $reg->target_class,
                    $reg->academic_year,
                    $reg->branch,
                    $reg->guardian_name,
                    $reg->guardian_email,
                    $reg->guardian_phone,
                    str_replace('Hubungan Wali: ', '', $reg->education_qualification),
                    str_replace(["\r", "\n"], ' ', $reg->residential_address),
                    $reg->created_at->format('Y-m-d H:i:s'),
                    strtoupper($reg->status)
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Menampilkan detail pendaftar spesifik
     */
    public function show(SantriRegistration $registration)
    {
        return view('admin.registrations.show', compact('registration'));
    }

    /**
     * Mengubah status pendaftar
     */
    public function updateStatus(Request $request, SantriRegistration $registration)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        $registration->update([
            'status' => $validated['status']
        ]);

        return redirect()->back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    /**
     * Memproses perubahan status massal untuk pendaftar yang terpilih
     */
    public function bulkStatus(Request $request)
    {
        $validated = $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:santri_registrations,id',
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        SantriRegistration::whereIn('id', $validated['registration_ids'])
            ->update(['status' => $validated['status']]);

        $statusLabel = match ($validated['status']) {
            'accepted' => 'diterima (approved)',
            'rejected' => 'ditolak (rejected)',
            default => 'menunggu (pending)',
        };

        return redirect()->back()->with('success', 'Status untuk ' . count($validated['registration_ids']) . ' pendaftar berhasil diubah menjadi ' . $statusLabel . '.');
    }

    /**
     * Merender halaman bukti pendaftaran khusus cetak/PDF
     */
    public function downloadPdf(SantriRegistration $registration)
    {
        $contents = \App\Models\PageContent::all()->keyBy('section_key');
        return view('admin.registrations.pdf', compact('registration', 'contents'));
    }
}
