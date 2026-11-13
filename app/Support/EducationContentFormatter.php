<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EducationContentFormatter
{
    /**
     * Format deskripsi program pendidikan menjadi pengantar singkat (rata kiri) dan poin-poin terstruktur.
     *
     * @return array{intro: list<string>, points_title: ?string, points: list<array{label: ?string, text: string}>}
     */
    public static function formatDescription(?string $description): array
    {
        $raw = trim((string) $description);
        if ($raw === '') {
            return ['intro' => [], 'points_title' => null, 'points' => []];
        }

        // Normalisasi bullet inline seperti "Judul: • Poin 1 • Poin 2" agar terpecah per baris
        $normalized = preg_replace('/\r\n|\r/', "\n", $raw);
        $normalized = preg_replace('/\s+([•●▪])\s*/u', "\n• ", $normalized);

        $lines = array_values(array_filter(
            array_map('trim', explode("\n", (string) $normalized)),
            fn (string $line): bool => $line !== ''
        ));

        $intro = [];
        $pointsTitle = null;
        $points = [];

        foreach ($lines as $line) {
            if (preg_match('/^(?:[•●▪\-\*]|\d+\.)\s*(.+)$/u', $line, $m)) {
                $item = trim($m[1]);
                if (str_contains($item, ':')) {
                    [$label, $body] = explode(':', $item, 2);
                    $label = trim($label);
                    $body = trim($body);
                    if ($label !== '' && mb_strlen($label) <= 60 && $body !== '') {
                        $points[] = ['label' => $label, 'text' => $body];

                        continue;
                    }
                }
                $points[] = ['label' => null, 'text' => $item];

                continue;
            }

            if ($points === [] && str_ends_with($line, ':') && mb_strlen($line) <= 80) {
                $pointsTitle = rtrim($line, ':');

                continue;
            }

            if ($points === [] && $pointsTitle === null) {
                // Jika kalimat tergabung bersama subjudul seperti "... Kemenag. Karakteristik Utama MTs:"
                if (preg_match('/^(.+?[.!?])\s+([A-Z][^.!?\n]{3,60}:)$/u', $line, $split)) {
                    $intro[] = trim($split[1]);
                    $pointsTitle = rtrim(trim($split[2]), ':');
                } else {
                    $intro[] = $line;
                }
            } else {
                $points[] = ['label' => null, 'text' => $line];
            }
        }

        return [
            'intro' => $intro,
            'points_title' => $pointsTitle,
            'points' => $points,
        ];
    }

    /**
     * Ringkasan pendek untuk kartu preview program pendidikan (1-2 kalimat pengantar).
     */
    public static function excerpt(?string $description, int $limit = 165): string
    {
        $parsed = self::formatDescription($description);
        $base = $parsed['intro'] !== [] ? implode(' ', $parsed['intro']) : strip_tags((string) $description);

        return Str::limit(trim($base), $limit);
    }

    /**
     * Tentukan label tombol CTA yang ringkas & kontekstual.
     */
    public static function registrationCtaLabel(string $programName): string
    {
        $lower = mb_strtolower(trim($programName));
        if (str_contains($lower, 'mts') || str_contains($lower, 'tsanawiyah')) {
            return 'Daftar MTs';
        }
        if (str_contains($lower, 'aliyah') || preg_match('/\bma\b/', $lower)) {
            return 'Daftar MA';
        }
        if (str_contains($lower, 'smk')) {
            return 'Daftar SMK';
        }

        return 'Daftar Sekarang';
    }

    /**
     * Bangun metadata preview dokumen biaya pendaftaran (Gambar, PDF, atau CSV).
     *
     * @return array{type: string, ext: string, label: string, filename: string, url: string, download_url: string, csv_headers: list<string>, csv_rows: list<list<string>>}|null
     */
    public static function buildFeeDocumentPreview(?string $path, ?string $label = null, ?string $downloadUrl = null): ?array
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) => 'image',
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['csv', 'txt'], true) => 'csv',
            default => 'file',
        };

        $csvHeaders = [];
        $csvRows = [];

        if ($type === 'csv') {
            [$csvHeaders, $csvRows] = self::parseCsvFromPublicDisk($path);
        }

        $url = asset('storage/'.ltrim($path, '/'));

        return [
            'type' => $type,
            'ext' => strtoupper($ext),
            'label' => $label ?: basename($path),
            'filename' => basename($path),
            'url' => $url,
            'download_url' => $downloadUrl ?: $url,
            'csv_headers' => $csvHeaders,
            'csv_rows' => $csvRows,
        ];
    }

    /**
     * Parse file CSV dari disk public dengan deteksi otomatis delimiter (, atau ;).
     *
     * @return array{0: list<string>, 1: list<list<string>>}
     */
    private static function parseCsvFromPublicDisk(string $path): array
    {
        $raw = Storage::disk('public')->get($path);
        if (! is_string($raw) || trim($raw) === '') {
            return [[], []];
        }

        // Hapus UTF-8 BOM jika ada
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;
        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $firstNonEmpty = '';
        foreach ($lines as $l) {
            if (trim($l) !== '') {
                $firstNonEmpty = $l;

                break;
            }
        }

        $delimiter = ',';
        if ($firstNonEmpty !== '' && substr_count($firstNonEmpty, ';') > substr_count($firstNonEmpty, ',')) {
            $delimiter = ';';
        } elseif ($firstNonEmpty !== '' && substr_count($firstNonEmpty, "\t") > substr_count($firstNonEmpty, ',')) {
            $delimiter = "\t";
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $raw);
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $cleaned = array_map(fn ($cell) => trim((string) $cell), $row);
            if (array_filter($cleaned, fn ($c) => $c !== '') !== []) {
                $rows[] = $cleaned;
            }
            if (count($rows) >= 201) {
                break;
            }
        }
        fclose($handle);

        if ($rows === []) {
            return [[], []];
        }

        $headers = array_shift($rows);
        $colCount = count($headers);
        $normalizedRows = array_map(function (array $row) use ($colCount): array {
            if (count($row) < $colCount) {
                return array_pad($row, $colCount, '');
            }

            return array_slice($row, 0, $colCount);
        }, $rows);

        return [$headers, $normalizedRows];
    }
}
