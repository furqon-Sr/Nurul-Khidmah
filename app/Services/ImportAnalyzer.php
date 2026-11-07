<?php

namespace App\Services;

use App\Models\EducationalUnit;
use App\Models\ImportBatch;
use App\Models\Kelas;
use App\Models\Pengajar;
use App\Models\Santri;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

/**
 * Validasi berkas import SEBELUM data masuk ke tabel utama (dry-run).
 *
 * Hasil analisis dipakai untuk pratinjau, laporan kesalahan, dan untuk menyusun
 * berkas bersih (hanya baris valid) yang kemudian diantrekan sebagai import sungguhan.
 */
class ImportAnalyzer
{
    /** Batas baris agar validasi sinkron tetap cepat; berkas lebih besar perlu dipecah. */
    public const MAX_ROWS = 5000;

    public const MAX_FILE_KB = 20480;

    public const EXTENSIONS = ['xlsx', 'xls', 'csv'];

    /**
     * Skema kolom per tipe data: kolom kanonik, wajib/opsional, contoh, dan alias header yang diterima.
     *
     * @return array<string, array{label:string, columns: array<string, array{required:bool, example:string, note:string, aliases:list<string>}>}>
     */
    public static function schemas(): array
    {
        return [
            ImportBatch::TYPE_SANTRI => [
                'label' => 'Santri',
                'key' => 'nis',
                'columns' => [
                    'nama_lengkap' => ['required' => true, 'example' => 'Ahmad Fulan', 'note' => 'Nama sesuai akta kelahiran.', 'aliases' => []],
                    'nis' => ['required' => false, 'example' => '26MTS001', 'note' => 'Kosongkan agar dibuat otomatis.', 'aliases' => []],
                    'jenis_kelamin' => ['required' => false, 'example' => 'Laki-laki', 'note' => 'Laki-laki / Perempuan (atau L / P).', 'aliases' => []],
                    'unit' => ['required' => false, 'example' => 'MTs', 'note' => 'Kode, nama, atau jenjang unit pendidikan.', 'aliases' => ['unit_pendidikan', 'kode_unit']],
                    'kelas' => ['required' => false, 'example' => '7A', 'note' => 'Nama atau kode kelas yang sudah ada.', 'aliases' => ['nama_kelas', 'kode_kelas']],
                    'tempat_lahir' => ['required' => false, 'example' => 'Lamongan', 'note' => '', 'aliases' => []],
                    'tanggal_lahir' => ['required' => false, 'example' => '2012-05-14', 'note' => 'Format YYYY-MM-DD.', 'aliases' => []],
                    'alamat' => ['required' => false, 'example' => 'Ds. Kalitengah, Lamongan', 'note' => '', 'aliases' => []],
                    'nama_wali' => ['required' => false, 'example' => 'H. Abdullah', 'note' => '', 'aliases' => []],
                    'no_hp_wali' => ['required' => false, 'example' => '081234567890', 'note' => 'Hanya angka, boleh diawali +.', 'aliases' => ['no_hp']],
                    'tahun_masuk' => ['required' => false, 'example' => '2026', 'note' => 'Empat digit.', 'aliases' => []],
                    'status_santri' => ['required' => false, 'example' => 'Aktif', 'note' => 'Kosongkan untuk Aktif.', 'aliases' => []],
                ],
            ],
            ImportBatch::TYPE_PENGAJAR => [
                'label' => 'Pengajar',
                'key' => 'nomor_identitas',
                'columns' => [
                    'nama_lengkap' => ['required' => true, 'example' => 'Ust. Muhammad Zaid', 'note' => 'Nama beserta gelar.', 'aliases' => []],
                    'nomor_identitas' => ['required' => false, 'example' => 'NIY-26-001', 'note' => 'Kosongkan agar dibuat otomatis.', 'aliases' => ['niy', 'nip']],
                    'jenis_kelamin' => ['required' => false, 'example' => 'Laki-laki', 'note' => 'Laki-laki / Perempuan (atau L / P).', 'aliases' => []],
                    'alamat' => ['required' => false, 'example' => 'Komplek Pesantren Putra', 'note' => '', 'aliases' => []],
                    'nomor_telepon' => ['required' => false, 'example' => '081321456789', 'note' => 'Hanya angka, boleh diawali +.', 'aliases' => ['no_hp']],
                    'status_pengajar' => ['required' => false, 'example' => 'Aktif', 'note' => 'Aktif atau Non-Aktif.', 'aliases' => []],
                ],
            ],
        ];
    }

    public static function schema(string $type): array
    {
        return self::schemas()[$type] ?? throw new RuntimeException('Tipe data import tidak dikenal.');
    }

    /**
     * @return array{headers:list<string>, missing:list<string>, unknown:list<string>, rows:list<array>, summary:array<string,int>}
     */
    public function analyze(string $type, string $path, string $disk = 'local'): array
    {
        $schema = self::schema($type);
        $rows = $this->readRows($path, $disk);

        $headers = $rows->isNotEmpty() ? $rows->first()->keys()->map(fn ($k) => (string) $k)->all() : [];
        $known = [];
        foreach ($schema['columns'] as $col => $def) {
            $known[] = $col;
            array_push($known, ...$def['aliases']);
        }
        $missing = collect($schema['columns'])
            ->filter(fn ($def, $col) => $def['required'] && ! array_intersect([$col, ...$def['aliases']], $headers))
            ->keys()->all();
        $unknown = array_values(array_diff(array_filter($headers, fn ($h) => $h !== '' && ! is_numeric($h)), $known));

        $result = ['headers' => $headers, 'missing' => $missing, 'unknown' => $unknown, 'rows' => [], 'summary' => []];
        if ($missing !== []) {
            $result['summary'] = $this->summarize([]);

            return $result;
        }

        $result['rows'] = $type === ImportBatch::TYPE_SANTRI
            ? $this->analyzeSantri($rows)
            : $this->analyzePengajar($rows);
        $result['summary'] = $this->summarize($result['rows']);

        return $result;
    }

    /**
     * Tulis berkas CSV bersih berisi baris yang akan benar-benar diimpor.
     *
     * @param  list<array>  $rows  hasil analyze()['rows']
     * @return int jumlah baris yang ditulis
     */
    public function writeCleanCsv(string $type, array $rows, string $path, string $mode, string $disk = 'local'): int
    {
        $columns = array_keys(self::schema($type)['columns']);
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $columns);

        $count = 0;
        foreach ($rows as $row) {
            if ($row['status'] === 'invalid') {
                continue;
            }
            if ($row['status'] === 'exists' && $mode !== ImportBatch::MODE_UPDATE_EXISTING) {
                continue;
            }
            fputcsv($handle, array_map(fn ($c) => $row['data'][$c] ?? '', $columns));
            $count++;
        }

        rewind($handle);
        Storage::disk($disk)->put($path, stream_get_contents($handle));
        fclose($handle);

        return $count;
    }

    private function readRows(string $path, string $disk): Collection
    {
        $reader = new class implements ToCollection, WithHeadingRow
        {
            public Collection $rows;

            public function collection(Collection $rows): void
            {
                $this->rows = $rows;
            }
        };

        try {
            Excel::import($reader, $path, $disk);
        } catch (\Throwable $e) {
            throw new RuntimeException('Berkas tidak dapat dibaca. Pastikan formatnya benar dan tidak rusak.', 0, $e);
        }

        $rows = ($reader->rows ?? collect())->filter(
            fn ($row) => $row->contains(fn ($v) => trim((string) $v) !== '')
        )->values();

        if ($rows->count() > self::MAX_ROWS) {
            throw new RuntimeException('Berkas berisi '.number_format($rows->count()).' baris, melebihi batas '.number_format(self::MAX_ROWS).' baris per unggahan. Pecah berkas menjadi beberapa bagian.');
        }

        return $rows;
    }

    // ───────────────────────── Santri ─────────────────────────

    private function analyzeSantri(Collection $rows): array
    {
        $existing = Santri::pluck('nis')->map(fn ($v) => (string) $v)->flip();
        $units = [];
        $classes = [];
        $seen = [];
        $out = [];

        foreach ($rows as $i => $raw) {
            $n = $i + 2; // baris 1 = header
            $r = $this->normalize($raw, ImportBatch::TYPE_SANTRI);
            $errors = [];
            $warnings = [];

            if ($r['nama_lengkap'] === '') {
                $errors[] = 'Nama lengkap wajib diisi.';
            } elseif (mb_strlen($r['nama_lengkap']) > 255) {
                $errors[] = 'Nama lengkap terlalu panjang (maks. 255 karakter).';
            }

            $this->checkGender($r['jenis_kelamin'], $errors, $warnings);
            $r['tanggal_lahir'] = $this->checkDate($r['tanggal_lahir'], $errors);

            if ($r['unit'] !== '') {
                $units[$r['unit']] ??= EducationalUnit::where('kode_unit', $r['unit'])
                    ->orWhere('nama_unit', 'like', "%{$r['unit']}%")
                    ->orWhere('jenjang', $r['unit'])->exists();
                if (! $units[$r['unit']]) {
                    $warnings[] = "Unit \"{$r['unit']}\" tidak ditemukan, unit bawaan akan dipakai.";
                }
            }
            if ($r['kelas'] !== '') {
                $classes[$r['kelas']] ??= Kelas::where('nama_kelas', 'like', "%{$r['kelas']}%")->orWhere('kode_kelas', $r['kelas'])->exists();
                if (! $classes[$r['kelas']]) {
                    $warnings[] = "Kelas \"{$r['kelas']}\" tidak ditemukan, kelas bawaan unit akan dipakai.";
                }
            }
            $this->checkPhone($r['no_hp_wali'], 'No. HP wali', $warnings);
            if ($r['tahun_masuk'] !== '' && ! preg_match('/^\d{4}$/', $r['tahun_masuk'])) {
                $errors[] = 'Tahun masuk harus 4 digit angka.';
            }

            $status = $this->resolveStatus($r['nis'], $existing, $seen, $n, 'NIS', $errors);
            $out[] = $this->row($n, $r, $errors, $warnings, $status, $r['nama_lengkap']);
        }

        return $out;
    }

    // ───────────────────────── Pengajar ─────────────────────────

    private function analyzePengajar(Collection $rows): array
    {
        $existing = Pengajar::pluck('nomor_identitas')->map(fn ($v) => (string) $v)->flip();
        $seen = [];
        $out = [];

        foreach ($rows as $i => $raw) {
            $n = $i + 2;
            $r = $this->normalize($raw, ImportBatch::TYPE_PENGAJAR);
            $errors = [];
            $warnings = [];

            if ($r['nama_lengkap'] === '') {
                $errors[] = 'Nama lengkap wajib diisi.';
            } elseif (mb_strlen($r['nama_lengkap']) > 255) {
                $errors[] = 'Nama lengkap terlalu panjang (maks. 255 karakter).';
            }

            $this->checkGender($r['jenis_kelamin'], $errors, $warnings);
            $this->checkPhone($r['nomor_telepon'], 'Nomor telepon', $warnings);

            if ($r['status_pengajar'] !== '' && ! in_array($r['status_pengajar'], ['Aktif', 'Non-Aktif'], true)) {
                $errors[] = 'Status pengajar harus "Aktif" atau "Non-Aktif".';
            }

            $status = $this->resolveStatus($r['nomor_identitas'], $existing, $seen, $n, 'Nomor identitas', $errors);
            $out[] = $this->row($n, $r, $errors, $warnings, $status, $r['nama_lengkap']);
        }

        return $out;
    }

    // ───────────────────────── Util ─────────────────────────

    /** Petakan alias header ke kolom kanonik dan rapikan nilainya menjadi string. */
    private function normalize($raw, string $type): array
    {
        $out = [];
        foreach (self::schema($type)['columns'] as $col => $def) {
            $value = '';
            foreach ([$col, ...$def['aliases']] as $key) {
                if (isset($raw[$key]) && trim((string) $raw[$key]) !== '') {
                    $value = $raw[$key];
                    break;
                }
            }
            $out[$col] = is_scalar($value) || $value === null ? trim((string) $value) : '';
        }

        return $out;
    }

    private function checkGender(string $value, array &$errors, array &$warnings): void
    {
        if ($value === '') {
            $warnings[] = 'Jenis kelamin kosong, akan diisi Laki-laki.';

            return;
        }
        if (! in_array(mb_strtolower($value), ['l', 'p', 'laki-laki', 'laki laki', 'perempuan'], true)) {
            $errors[] = "Jenis kelamin \"{$value}\" tidak dikenal (gunakan Laki-laki/Perempuan atau L/P).";
        }
    }

    private function checkDate(string $value, array &$errors): string
    {
        if ($value === '') {
            return '';
        }
        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 80000) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        $ts = strtotime($value);
        if ($ts === false) {
            $errors[] = "Tanggal lahir \"{$value}\" tidak valid (gunakan YYYY-MM-DD).";

            return $value;
        }

        return date('Y-m-d', $ts);
    }

    private function checkPhone(string $value, string $label, array &$warnings): void
    {
        if ($value !== '' && ! preg_match('/^\+?[0-9\s\-()]{8,20}$/', $value)) {
            $warnings[] = "{$label} \"{$value}\" tampak tidak valid.";
        }
    }

    /** Tentukan status baris berdasarkan kunci unik: valid, exists (sudah ada di database), atau invalid. */
    private function resolveStatus(string $key, Collection $existing, array &$seen, int $n, string $label, array &$errors): string
    {
        if ($key !== '') {
            if (isset($seen[$key])) {
                $errors[] = "{$label} \"{$key}\" duplikat dengan baris {$seen[$key]} pada berkas ini.";
            } else {
                $seen[$key] = $n;
            }
        }
        if ($errors !== []) {
            return 'invalid';
        }

        return ($key !== '' && $existing->has($key)) ? 'exists' : 'valid';
    }

    private function row(int $n, array $data, array $errors, array $warnings, string $status, string $name): array
    {
        return ['n' => $n, 'name' => $name, 'key' => $data['nis'] ?? $data['nomor_identitas'] ?? '', 'data' => $data, 'errors' => $errors, 'warnings' => $warnings, 'status' => $status];
    }

    private function summarize(array $rows): array
    {
        $count = fn (string $s) => count(array_filter($rows, fn ($r) => $r['status'] === $s));

        return [
            'total' => count($rows),
            'valid' => $count('valid'),
            'exists' => $count('exists'),
            'invalid' => $count('invalid'),
            'warnings' => count(array_filter($rows, fn ($r) => $r['status'] !== 'invalid' && $r['warnings'] !== [])),
        ];
    }
}
