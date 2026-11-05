<?php

namespace App\Http\Controllers;

use App\Actions\AcceptSantriRegistration;
use App\Models\AdminActivityLog;
use App\Models\EducationProgram;
use App\Models\Kelas;
use App\Models\PageContent;
use App\Models\Santri;
use App\Models\SantriRegistration;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SantriRegistrationController extends Controller
{
    public function __construct(
        protected AcceptSantriRegistration $acceptSantriRegistration = new AcceptSantriRegistration
    ) {}

    /**
     * Menampilkan daftar semua pendaftar dengan fitur pencarian dan filter
     */
    public function index(Request $request)
    {
        $query = SantriRegistration::with('santri');

        // Ambil tahun ajaran aktif dari database (sinkron PageContent & SIAKAD TahunAjaran)
        $activeYear = PageContent::where('section_key', 'active_academic_year')->value('content_text')
            ?: TahunAjaran::getAktif()?->nama_tahun_ajaran
            ?: (date('Y').'/'.(date('Y') + 1));

        // Ambil semua daftar unik tahun ajaran dari database untuk filter
        $academicYears = SantriRegistration::distinct()->pluck('academic_year')->filter()->toArray();
        if (! in_array($activeYear, $academicYears)) {
            $academicYears[] = $activeYear;
        }
        sort($academicYears);

        // Pilih tahun ajaran filter (default ke tahun aktif saat ini)
        $selectedYear = $request->input('academic_year', $activeYear);

        if ($selectedYear !== 'all') {
            $query->where(function ($q) use ($selectedYear, $activeYear) {
                $q->where('academic_year', $selectedYear);
                if ($selectedYear === $activeYear) {
                    $q->orWhereNull('academic_year');
                }
            });
        }

        // Pencarian berdasarkan nama, nomor registrasi, atau nama wali
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
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

        // Ekspor ke file Excel (CSV terpisah semicolon dengan UTF-8 BOM & sep=;) jika parameter action = export
        if ($request->input('action') === 'export') {
            AdminActivityLog::log('export', 'Mengekspor data pendaftar PPDB ke CSV/Excel ('.$selectedYear.')', 'SantriRegistration');

            return $this->exportExcel($query->latest()->get());
        }

        $registrations = $query->latest()->paginate(15)->withQueryString();

        $classes = EducationProgram::active()->orderBy('order')->pluck('name');

        $statsQuery = SantriRegistration::query();
        if ($selectedYear !== 'all') {
            $statsQuery->where(function ($q) use ($selectedYear, $activeYear) {
                $q->where('academic_year', $selectedYear);
                if ($selectedYear === $activeYear) {
                    $q->orWhereNull('academic_year');
                }
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'accepted' => (clone $statsQuery)->where('status', 'accepted')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        return view('admin.registrations.index', compact('registrations', 'classes', 'stats', 'academicYears', 'selectedYear', 'activeYear'));
    }

    /**
     * Ekspor data pendaftar ke file CSV kompatibel penuh dengan Excel
     */
    protected function exportExcel($registrations)
    {
        $filename = 'Data_Pendaftar_'.date('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($registrations) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fwrite($file, "sep=;\n");

            fputcsv($file, [
                'No. Registrasi',
                'NIS (Jika Diterima)',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Tanggal Lahir',
                'Tujuan Kelas / Jenjang',
                'Tahun Ajaran',
                'Nama Orang Tua (Ayah & Ibu)',
                'Email Pendaftar',
                'No. WhatsApp Pendaftar',
                'Alamat Domisili',
                'Tanggal Pendaftaran',
                'Status',
            ], ';');

            foreach ($registrations as $reg) {
                fputcsv($file, [
                    $reg->registration_number,
                    $reg->santri->nis ?? '-',
                    $reg->student_name,
                    $reg->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                    $reg->birth_date,
                    $reg->target_class,
                    $reg->academic_year,
                    $reg->guardian_name,
                    $reg->guardian_email,
                    $reg->guardian_phone,
                    str_replace(["\r", "\n"], ' ', $reg->residential_address),
                    $reg->created_at->format('Y-m-d H:i:s'),
                    strtoupper($reg->status),
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Menampilkan detail pendaftar spesifik beserta info akun Wali jika sudah diterima
     */
    public function show(SantriRegistration $registration)
    {
        $registration->load(['santri.user', 'santri.kelas']);

        return view('admin.registrations.show', compact('registration'));
    }

    /**
     * Mengubah status pendaftar & konversi otomatis ke akun Wali Santri saat diterima (FR-02)
     */
    public function updateStatus(Request $request, SantriRegistration $registration)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        $credentialMsg = '';
        if ($validated['status'] === 'accepted') {
            $info = $this->acceptSantriRegistration->execute($registration);
            if (! empty($info['is_new_user'])) {
                $credentialMsg = " Akun Portal Wali Santri baru diterbitkan! NIS: {$info['nis']} | Email: {$info['email']} | Password Sementara: {$info['password']} (Wajib ganti password saat login pertama).";
            } else {
                $credentialMsg = " Santri berhasil dihubungkan ke akun Wali Santri yang sudah ada ({$info['email']})! NIS: {$info['nis']} (Gunakan kata sandi wali yang telah terdaftar).";
            }
        } else {
            $registration->update([
                'status' => $validated['status'],
            ]);
        }

        AdminActivityLog::log(
            'update',
            "Mengubah status PPDB {$registration->student_name} ({$registration->registration_number}) menjadi ".strtoupper($validated['status']),
            'SantriRegistration',
            $registration->id
        );

        return redirect()->back()->with('success', 'Status pendaftaran berhasil diperbarui.'.$credentialMsg);
    }

    /**
     * Memproses perubahan status massal untuk pendaftar yang terpilih
     */
    public function bulkStatus(Request $request)
    {
        $validated = $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:santri_registrations,id',
            'status' => 'required|in:pending,accepted,rejected,delete',
        ]);

        if ($validated['status'] === 'delete') {
            $registrations = SantriRegistration::whereIn('id', $validated['registration_ids'])->get();
            $count = $registrations->count();

            foreach ($registrations as $reg) {
                if ($reg->student_photo && Storage::disk('public')->exists($reg->student_photo)) {
                    Storage::disk('public')->delete($reg->student_photo);
                }
                $reg->delete();
            }

            AdminActivityLog::log('delete', "Menghapus massal {$count} data pendaftar PPDB", 'SantriRegistration');

            return redirect()->back()->with('success', $count.' data pendaftar berhasil dihapus secara permanen.');
        }

        $registrations = SantriRegistration::whereIn('id', $validated['registration_ids'])->get();
        foreach ($registrations as $reg) {
            if ($validated['status'] === 'accepted') {
                $this->acceptSantriRegistration->execute($reg);
            } else {
                $reg->update(['status' => $validated['status']]);
            }
        }

        AdminActivityLog::log(
            'update',
            'Mengubah status massal '.count($validated['registration_ids']).' pendaftar PPDB menjadi '.strtoupper($validated['status']),
            'SantriRegistration'
        );

        $statusLabel = match ($validated['status']) {
            'accepted' => 'diterima & diproses akun Portal Wali',
            'rejected' => 'ditolak (rejected)',
            default => 'menunggu (pending)',
        };

        return redirect()->back()->with('success', 'Status untuk '.count($validated['registration_ids']).' pendaftar berhasil diubah menjadi '.$statusLabel.'.');
    }

    /**
     * Otomatis menerbitkan NIS, menyimpan data Santri aktif, dan akun Wali via AcceptSantriRegistration (FR-02).
     */
    public function convertToSantriAccount(SantriRegistration $registration): array
    {
        return $this->acceptSantriRegistration->execute($registration);
    }

    /**
     * Menghapus pendaftar tunggal beserta fotonya jika ada
     */
    public function destroy(SantriRegistration $registration)
    {
        if ($registration->student_photo && Storage::disk('public')->exists($registration->student_photo)) {
            Storage::disk('public')->delete($registration->student_photo);
        }

        $name = $registration->student_name;
        $regNo = $registration->registration_number;
        $registration->delete();

        AdminActivityLog::log('delete', "Menghapus data pendaftar PPDB: {$name} ({$regNo})", 'SantriRegistration');

        return redirect()->route('admin.registrations.index')->with('success', 'Data pendaftar berhasil dihapus secara permanen.');
    }

    /**
     * Merender halaman bukti pendaftaran & informasi akun daftar ulang khusus cetak/PDF
     */
    public function downloadPdf(SantriRegistration $registration)
    {
        $registration->load(['santri.user', 'santri.kelas']);
        $contents = PageContent::all()->keyBy('section_key');

        return view('admin.registrations.pdf', compact('registration', 'contents'));
    }
}
