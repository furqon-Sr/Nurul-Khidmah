<?php

namespace App\Imports;

use App\Models\ImportBatch;
use App\Models\Pengajar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\ImportFailed;

class PengajarImport implements ShouldQueue, ToModel, WithChunkReading, WithEvents, WithHeadingRow, WithValidation
{
    use Queueable;

    public ?int $batchId;

    public function __construct(?int $batchId = null)
    {
        $this->batchId = $batchId;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string',
            'nomor_identitas' => 'nullable|string',
        ];
    }

    public function model(array $row): Model|array|null
    {
        $nama = trim($row['nama_lengkap'] ?? '');
        if ($nama === '') {
            return null;
        }

        $nomor = trim($row['nomor_identitas'] ?? $row['niy'] ?? $row['nip'] ?? '');
        if ($nomor === '') {
            $nomor = $this->generateNomorIdentitas();
        }

        $jkRaw = strtoupper(trim($row['jenis_kelamin'] ?? ''));
        $jenisKelamin = ($jkRaw === 'P' || str_contains($jkRaw, 'PEREMPUAN')) ? 'Perempuan' : 'Laki-laki';

        if ($this->batchId) {
            ImportBatch::where('id', $this->batchId)->increment('processed_rows');
        }

        $pengajar = Pengajar::firstOrNew(['nomor_identitas' => $nomor]);
        $pengajar->fill([
            'nama_lengkap' => $nama,
            'jenis_kelamin' => $jenisKelamin,
            'alamat' => $row['alamat'] ?? null,
            'nomor_telepon' => $row['nomor_telepon'] ?? $row['no_hp'] ?? null,
            'status_pengajar' => $row['status_pengajar'] ?? 'Aktif',
        ]);

        return $pengajar;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                if ($this->batchId) {
                    $highestRow = $event->sheet->getDelegate()->getHighestDataRow();
                    $total = max(0, $highestRow - 1);
                    ImportBatch::where('id', $this->batchId)->update([
                        'total_rows' => $total,
                        'status' => ImportBatch::STATUS_PROCESSING,
                    ]);
                }
            },
            AfterImport::class => function (AfterImport $event) {
                if ($this->batchId) {
                    $batch = ImportBatch::find($this->batchId);
                    if ($batch) {
                        $batch->update([
                            'status' => ImportBatch::STATUS_COMPLETED,
                            'total_rows' => $batch->total_rows ?: $batch->processed_rows,
                        ]);
                    }
                }
            },
            ImportFailed::class => function (ImportFailed $event) {
                if ($this->batchId) {
                    $batch = ImportBatch::find($this->batchId);
                    if ($batch) {
                        $errs = $batch->errors ?? [];
                        $errs[] = $event->getException()->getMessage();
                        $batch->update([
                            'status' => ImportBatch::STATUS_FAILED,
                            'errors' => $errs,
                        ]);
                    }
                }
            },
        ];
    }

    /**
     * Generate Nomor Identitas Pengajar otomatis format NIY-[Tahun2Digit]-[3DigitUrutan], contoh: NIY-26-001
     */
    protected function generateNomorIdentitas(): string
    {
        $prefix = 'NIY-'.date('y');
        $latest = Pengajar::where('nomor_identitas', 'like', $prefix.'%')->orderByDesc('nomor_identitas')->first();
        $maxSeq = 0;
        if ($latest && preg_match('/^'.preg_quote($prefix, '/').'-?(\d+)$/', $latest->nomor_identitas, $m)) {
            $maxSeq = (int) $m[1];
        }

        $seq = $maxSeq + 1;
        $nomor = $prefix.'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        while (Pengajar::where('nomor_identitas', $nomor)->exists()) {
            $seq++;
            $nomor = $prefix.'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        }

        return $nomor;
    }
}
