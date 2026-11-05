<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\PengajarImport;
use App\Imports\SantriImport;
use App\Models\ImportBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportDataController extends Controller
{
    /**
     * Menampilkan dashboard import data & riwayat batch migrasi.
     */
    public function index()
    {
        $batches = ImportBatch::with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.imports.index', compact('batches'));
    }

    /**
     * Upload data santri dan masukkan job ke background queue.
     */
    public function uploadSantri(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
        ], [
            'file.required' => 'Silakan pilih berkas Excel atau CSV santri terlebih dahulu.',
            'file.mimes' => 'Format berkas harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran berkas maksimal adalah 20 MB.',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $storedPath = $file->storeAs('imports', time().'_'.$originalName, 'local');

        // Estimasi jumlah baris awal jika file teks/CSV
        $totalRows = 0;
        if (Storage::disk('local')->exists($storedPath) && (str_ends_with(strtolower($originalName), '.csv') || str_ends_with(strtolower($originalName), '.txt'))) {
            $content = Storage::disk('local')->get($storedPath);
            $lines = array_filter(explode("\n", trim($content)));
            $totalRows = max(0, count($lines) - 1);
        }

        $batch = ImportBatch::create([
            'file_name' => $originalName,
            'file_path' => $storedPath,
            'type' => ImportBatch::TYPE_SANTRI,
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'status' => ImportBatch::STATUS_PENDING,
            'created_by' => Auth::id(),
        ]);

        Excel::queueImport(new SantriImport($batch->id), $storedPath, 'local');

        return redirect()->route('admin.imports.index')->with(
            'success',
            "Berkas '{$originalName}' berhasil dijadwalkan ke antrean. Migrasi data santri sedang diproses di background queue."
        );
    }

    /**
     * Upload data pengajar dan masukkan job ke background queue.
     */
    public function uploadPengajar(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
        ], [
            'file.required' => 'Silakan pilih berkas Excel atau CSV pengajar terlebih dahulu.',
            'file.mimes' => 'Format berkas harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran berkas maksimal adalah 20 MB.',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $storedPath = $file->storeAs('imports', time().'_'.$originalName, 'local');

        $totalRows = 0;
        if (Storage::disk('local')->exists($storedPath) && (str_ends_with(strtolower($originalName), '.csv') || str_ends_with(strtolower($originalName), '.txt'))) {
            $content = Storage::disk('local')->get($storedPath);
            $lines = array_filter(explode("\n", trim($content)));
            $totalRows = max(0, count($lines) - 1);
        }

        $batch = ImportBatch::create([
            'file_name' => $originalName,
            'file_path' => $storedPath,
            'type' => ImportBatch::TYPE_PENGAJAR,
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'status' => ImportBatch::STATUS_PENDING,
            'created_by' => Auth::id(),
        ]);

        Excel::queueImport(new PengajarImport($batch->id), $storedPath, 'local');

        return redirect()->route('admin.imports.index')->with(
            'success',
            "Berkas '{$originalName}' berhasil dijadwalkan ke antrean. Migrasi data pengajar sedang diproses di background queue."
        );
    }

    /**
     * Download format/template CSV untuk memudahkan input Tata Usaha.
     */
    public function downloadTemplate(string $type)
    {
        if ($type === 'santri') {
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="template_import_santri.csv"'];
            $content = "nama_lengkap,nis,jenis_kelamin,unit,kelas,tempat_lahir,tanggal_lahir,alamat,nama_wali,no_hp_wali,tahun_masuk\n".
                       "Ahmad Fulan,26MTS001,Laki-laki,MTs,7A,Lamongan,2012-05-14,Ds. Kalitengah Lamongan,H. Abdullah,081234567890,2026\n".
                       "Fathimah Az-Zahra,,Perempuan,MA,10A,Lamongan,2010-09-21,Kec. Sukodadi,Hj. Maryam,081298765432,2026\n";

            return response($content, 200, $headers);
        }

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="template_import_pengajar.csv"'];
        $content = "nama_lengkap,nomor_identitas,jenis_kelamin,alamat,nomor_telepon,status_pengajar\n".
                   "Ust. Muhammad Zaid,NIY-26-001,Laki-laki,Komplek Pesantren Putra,081321456789,Aktif\n".
                   "Ustazah Khodijah,,Perempuan,Komplek Pesantren Putri,081398765432,Aktif\n";

        return response($content, 200, $headers);
    }
}
