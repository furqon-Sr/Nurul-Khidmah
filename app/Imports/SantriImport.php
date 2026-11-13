<?php

namespace App\Imports;

use App\Models\EducationalUnit;
use App\Models\ImportBatch;
use App\Models\Kelas;
use App\Models\Santri;
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

class SantriImport implements ShouldQueue, ToModel, WithChunkReading, WithEvents, WithHeadingRow, WithValidation
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
            'nama_lengkap' => 'required_without:nama|nullable|string',
            'nama' => 'required_without:nama_lengkap|nullable|string',
            'nis' => 'nullable|string',
            'nisn' => 'nullable|string',
        ];
    }

    public function model(array $row): Model|array|null
    {
        $nama = trim($row['nama_lengkap'] ?? $row['nama'] ?? $row['nama_santri'] ?? $row['nama_siswa'] ?? '');
        if ($nama === '') {
            return null;
        }

        // 1. Hubungkan educational_unit_id
        $unit = null;
        $rawUnit = trim($row['unit'] ?? $row['unit_pendidikan'] ?? $row['kode_unit'] ?? '');
        if ($rawUnit !== '') {
            $unit = EducationalUnit::where('kode_unit', $rawUnit)
                ->orWhere('nama_unit', 'like', "%{$rawUnit}%")
                ->orWhere('jenjang', $rawUnit)
                ->first();
        }

        // 2. Hubungkan kelas_id
        $kelas = null;
        $rawKelas = trim($row['kelas'] ?? $row['nama_kelas'] ?? $row['kode_kelas'] ?? '');
        if ($rawKelas !== '') {
            $kelas = Kelas::where('nama_kelas', 'like', "%{$rawKelas}%")
                ->orWhere('kode_kelas', $rawKelas)
                ->first();
        }

        if ($kelas && ! $unit && $kelas->educational_unit_id) {
            $unit = EducationalUnit::find($kelas->educational_unit_id);
        }
        if (! $unit) {
            $unit = EducationalUnit::first();
        }
        if (! $kelas && $unit) {
            $kelas = Kelas::where('educational_unit_id', $unit->id)->first();
        }

        // 3. Otomatis generate NIS jika kosong
        $nis = trim($row['nis'] ?? '');
        if ($nis === '') {
            $nis = $this->generateNis($unit, $row['tahun_masuk'] ?? null);
        }

        // 4. Format jenis kelamin
        $jkRaw = strtoupper(trim($row['jenis_kelamin'] ?? ''));
        $jenisKelamin = ($jkRaw === 'P' || str_contains($jkRaw, 'PEREMPUAN')) ? 'Perempuan' : 'Laki-laki';

        // 5. Update progress di tabel import_batches
        if ($this->batchId) {
            ImportBatch::where('id', $this->batchId)->increment('processed_rows');
        }

        $nisn = trim((string) ($row['nisn'] ?? ''));

        $santri = Santri::firstOrNew(['nis' => $nis]);
        $santri->fill([
            'nisn' => $nisn !== '' ? $nisn : ($santri->nisn ?? null),
            'nama_lengkap' => $nama,
            'educational_unit_id' => $unit?->id,
            'kelas_id' => $kelas?->id,
            'tempat_lahir' => $row['tempat_lahir'] ?? null,
            'tanggal_lahir' => ! empty($row['tanggal_lahir']) ? date('Y-m-d', strtotime($row['tanggal_lahir'])) : null,
            'jenis_kelamin' => $jenisKelamin,
            'alamat' => $row['alamat'] ?? null,
            'nama_wali' => $row['nama_wali'] ?? null,
            'no_hp_wali' => $row['no_hp_wali'] ?? $row['no_hp'] ?? null,
            'tahun_masuk' => $row['tahun_masuk'] ?? date('Y'),
            'status_santri' => $row['status_santri'] ?? 'Aktif',
        ]);

        return $santri;
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
                            'status' => $batch->invalid_rows > 0 ? ImportBatch::STATUS_COMPLETED_WITH_ERRORS : ImportBatch::STATUS_COMPLETED,
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
     * Generate NIS otomatis format [Tahun2Digit][KodeUnit][3DigitUrutan], contoh: 26MTS001
     */
    protected function generateNis(?EducationalUnit $unit, ?string $tahunMasuk = null): string
    {
        $unitCode = $unit ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $unit->kode_unit)) : 'MTS';

        if (str_contains($unitCode, 'MTS')) {
            $cleanCode = 'MTS';
        } elseif (str_contains($unitCode, 'MA')) {
            $cleanCode = 'MA';
        } elseif (str_contains($unitCode, 'SMK')) {
            $cleanCode = 'SMK';
        } else {
            $cleanCode = substr($unitCode, 0, 3) ?: 'MTS';
        }

        $year = $tahunMasuk && strlen($tahunMasuk) >= 4
            ? substr($tahunMasuk, 2, 2)
            : date('y');

        $prefix = $year.$cleanCode;

        $latest = Santri::where('nis', 'like', $prefix.'%')->orderByDesc('nis')->first();
        $maxSeq = 0;
        if ($latest && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $latest->nis, $m)) {
            $maxSeq = (int) $m[1];
        }

        $seq = $maxSeq + 1;
        $nis = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        while (Santri::where('nis', $nis)->exists()) {
            $seq++;
            $nis = $prefix.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        }

        return $nis;
    }
}
