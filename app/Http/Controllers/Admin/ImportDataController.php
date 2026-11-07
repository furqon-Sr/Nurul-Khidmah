<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\PengajarImport;
use App\Imports\SantriImport;
use App\Models\ImportBatch;
use App\Services\ImportAnalyzer;
use App\Services\QueueHealth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Alur import bertahap: Unggah → Validasi → Pratinjau → Import.
 * Data tidak pernah masuk ke tabel utama sebelum pengguna mengonfirmasi hasil validasi.
 */
class ImportDataController extends Controller
{
    /** Dashboard import + riwayat batch (dengan filter status & tipe data). */
    public function index(Request $request): View
    {
        QueueHealth::probe();

        $filters = [
            'status' => $request->string('status')->toString(),
            'type' => $request->string('type')->toString(),
        ];

        $batches = ImportBatch::with('creator')
            ->when($filters['status'] !== '', fn ($q) => $q->where('status', $filters['status']))
            ->when(in_array($filters['type'], [ImportBatch::TYPE_SANTRI, ImportBatch::TYPE_PENGAJAR], true), fn ($q) => $q->where('type', $filters['type']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.imports.index', [
            'batches' => $batches,
            'filters' => $filters,
            'service' => QueueHealth::status(),
            'schemas' => ImportAnalyzer::schemas(),
            'maxFileMb' => ImportAnalyzer::MAX_FILE_KB / 1024,
            'maxRows' => ImportAnalyzer::MAX_ROWS,
            'activeType' => in_array(old('type', $request->query('type')), [ImportBatch::TYPE_SANTRI, ImportBatch::TYPE_PENGAJAR], true)
                ? old('type', $request->query('type'))
                : ImportBatch::TYPE_SANTRI,
        ]);
    }

    /** Polling ringan: status layanan antrean + progres batch yang sedang tampil. */
    public function status(Request $request): JsonResponse
    {
        QueueHealth::probe();

        $ids = array_filter(array_map('intval', (array) $request->query('ids', [])));
        $batches = ImportBatch::whereIn('id', $ids)->get()->map->toStatusPayload()->values();

        return response()->json([
            'service' => QueueHealth::status(),
            'batches' => $batches,
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    public function uploadSantri(Request $request): RedirectResponse
    {
        return $this->stage($request, ImportBatch::TYPE_SANTRI);
    }

    public function uploadPengajar(Request $request): RedirectResponse
    {
        return $this->stage($request, ImportBatch::TYPE_PENGAJAR);
    }

    /**
     * Langkah 1–2: simpan berkas lalu validasi (dry-run). Belum ada data yang masuk ke tabel utama.
     */
    private function stage(Request $request, string $type): RedirectResponse
    {
        $label = ImportAnalyzer::schema($type)['label'];

        $request->validate([
            'file' => ['required', 'file', 'mimes:'.implode(',', ImportAnalyzer::EXTENSIONS), 'max:'.ImportAnalyzer::MAX_FILE_KB],
        ], [
            'file.required' => "Silakan pilih berkas Excel atau CSV {$label} terlebih dahulu.",
            'file.mimes' => 'Format berkas harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran berkas maksimal adalah '.(ImportAnalyzer::MAX_FILE_KB / 1024).' MB.',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'berkas';
        $storedPath = $file->storeAs('imports', time().'_'.$safeName.'.'.strtolower($file->getClientOriginalExtension()), 'local');

        try {
            $analysis = app(ImportAnalyzer::class)->analyze($type, $storedPath);
        } catch (RuntimeException $e) {
            Storage::disk('local')->delete($storedPath);

            return back()->withErrors(['file' => $e->getMessage()])->withInput(['type' => $type]);
        }

        if ($analysis['missing'] !== []) {
            Storage::disk('local')->delete($storedPath);

            return back()->withErrors(['file' => 'Kolom wajib tidak ditemukan: '.implode(', ', $analysis['missing']).'. Unduh template agar format kolom sesuai.'])->withInput(['type' => $type]);
        }

        if ($analysis['summary']['total'] === 0) {
            Storage::disk('local')->delete($storedPath);

            return back()->withErrors(['file' => 'Berkas tidak berisi baris data. Isi data di bawah baris header.'])->withInput(['type' => $type]);
        }

        $summary = $analysis['summary'];
        $batch = ImportBatch::create([
            'file_name' => $originalName,
            'file_path' => $storedPath,
            'type' => $type,
            'total_rows' => $summary['total'],
            'processed_rows' => 0,
            'valid_rows' => $summary['valid'],
            'invalid_rows' => $summary['invalid'],
            'duplicate_rows' => $summary['exists'],
            'status' => ImportBatch::STATUS_VALIDATED,
            'report' => [
                'summary' => $summary,
                'unknown_columns' => $analysis['unknown'],
                'issues' => collect($analysis['rows'])
                    ->filter(fn ($r) => $r['status'] !== 'valid' || $r['warnings'] !== [])
                    ->map(fn ($r) => Arr::only($r, ['n', 'name', 'key', 'status', 'errors', 'warnings']))
                    ->values()->all(),
            ],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.imports.show', $batch)->with('success', 'Validasi selesai. Periksa hasilnya sebelum data diimpor.');
    }

    /** Langkah 3: pratinjau hasil validasi / detail batch. */
    public function show(Request $request, ImportBatch $batch): View
    {
        $filter = $request->string('filter', 'all')->toString();
        $issues = collect($batch->report['issues'] ?? [])
            ->when($filter === 'invalid', fn ($c) => $c->where('status', 'invalid'))
            ->when($filter === 'exists', fn ($c) => $c->where('status', 'exists'))
            ->when($filter === 'warning', fn ($c) => $c->filter(fn ($r) => $r['warnings'] !== []));

        return view('admin.imports.show', [
            'batch' => $batch->load('creator'),
            'issues' => $issues->take(200)->values(),
            'issueTotal' => $issues->count(),
            'filter' => $filter,
            'schema' => ImportAnalyzer::schema($batch->type),
        ]);
    }

    /** Langkah 4: konfirmasi → susun berkas bersih (hanya baris layak) lalu antrekan import. */
    public function confirm(Request $request, ImportBatch $batch): RedirectResponse
    {
        abort_unless($batch->isAwaitingConfirmation(), 409, 'Batch ini sudah dikonfirmasi atau dibatalkan.');

        $data = $request->validate([
            'mode' => ['required', Rule::in([ImportBatch::MODE_SKIP_EXISTING, ImportBatch::MODE_UPDATE_EXISTING])],
        ], ['mode.required' => 'Pilih cara menangani data yang sudah ada.']);

        $analyzer = app(ImportAnalyzer::class);

        try {
            $analysis = $analyzer->analyze($batch->type, $batch->file_path);
        } catch (RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $cleanPath = 'imports/clean_'.$batch->id.'.csv';
        $count = $analyzer->writeCleanCsv($batch->type, $analysis['rows'], $cleanPath, $data['mode']);

        if ($count === 0) {
            Storage::disk('local')->delete($cleanPath);

            return back()->withErrors(['mode' => 'Tidak ada baris yang dapat diimpor dengan pilihan ini.']);
        }

        $skipped = $data['mode'] === ImportBatch::MODE_SKIP_EXISTING ? $analysis['summary']['exists'] : 0;

        $batch->update([
            'mode' => $data['mode'],
            'total_rows' => $count,
            'processed_rows' => 0,
            'skipped_rows' => $skipped,
            'invalid_rows' => $analysis['summary']['invalid'],
            'status' => ImportBatch::STATUS_PENDING,
        ]);

        $import = $batch->type === ImportBatch::TYPE_SANTRI ? new SantriImport($batch->id) : new PengajarImport($batch->id);
        Excel::queueImport($import, $cleanPath, 'local');

        return redirect()->route('admin.imports.index')->with(
            'success',
            "{$count} baris {$batch->type_label} dijadwalkan ke antrean. Pantau progresnya pada riwayat di bawah."
        );
    }

    /** Batalkan batch yang belum dikonfirmasi: berkas dihapus, tidak ada data yang masuk. */
    public function cancel(ImportBatch $batch): RedirectResponse
    {
        abort_unless($batch->isAwaitingConfirmation(), 409, 'Hanya batch yang menunggu konfirmasi yang dapat dibatalkan.');

        Storage::disk('local')->delete(array_filter([$batch->file_path]));
        $batch->update(['status' => ImportBatch::STATUS_CANCELLED]);

        return redirect()->route('admin.imports.index')->with('success', "Import '{$batch->file_name}' dibatalkan. Tidak ada data yang diubah.");
    }

    /** Unduh laporan kesalahan & peringatan (CSV). */
    public function errors(ImportBatch $batch): StreamedResponse
    {
        $rows = collect($batch->report['issues'] ?? [])->filter(fn ($r) => $r['errors'] !== [] || $r['warnings'] !== []);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar terbaca benar di Excel
            fputcsv($out, ['Baris', 'Jenis', 'Nama', 'Kunci (NIS/Nomor identitas)', 'Pesan']);
            foreach ($rows as $r) {
                foreach ($r['errors'] as $msg) {
                    fputcsv($out, [$r['n'], 'Kesalahan', $r['name'], $r['key'], $msg]);
                }
                foreach ($r['warnings'] as $msg) {
                    fputcsv($out, [$r['n'], 'Peringatan', $r['name'], $r['key'], $msg]);
                }
                if ($r['status'] === 'exists') {
                    fputcsv($out, [$r['n'], 'Info', $r['name'], $r['key'], 'Data sudah terdaftar.']);
                }
            }
            fclose($out);
        }, 'laporan_kesalahan_import_'.$batch->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Download template CSV beserta contoh isi data. */
    public function downloadTemplate(string $type)
    {
        abort_unless(in_array($type, [ImportBatch::TYPE_SANTRI, ImportBatch::TYPE_PENGAJAR], true), 404);

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="template_import_'.$type.'.csv"'];

        if ($type === ImportBatch::TYPE_SANTRI) {
            $content = "nama_lengkap,nis,jenis_kelamin,unit,kelas,tempat_lahir,tanggal_lahir,alamat,nama_wali,no_hp_wali,tahun_masuk\n".
                       "Ahmad Fulan,26MTS001,Laki-laki,MTs,7A,Lamongan,2012-05-14,Ds. Kalitengah Lamongan,H. Abdullah,081234567890,2026\n".
                       "Fathimah Az-Zahra,,Perempuan,MA,10A,Lamongan,2010-09-21,Kec. Sukodadi,Hj. Maryam,081298765432,2026\n";

            return response($content, 200, $headers);
        }

        $content = "nama_lengkap,nomor_identitas,jenis_kelamin,alamat,nomor_telepon,status_pengajar\n".
                   "Ust. Muhammad Zaid,NIY-26-001,Laki-laki,Komplek Pesantren Putra,081321456789,Aktif\n".
                   "Ustazah Khodijah,,Perempuan,Komplek Pesantren Putri,081398765432,Aktif\n";

        return response($content, 200, $headers);
    }
}
