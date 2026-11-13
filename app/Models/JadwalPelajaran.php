<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalPelajaran extends Model
{
    use HasFactory;

    private const DAY_NAMES_BY_WEEKDAY = [
        CarbonInterface::SUNDAY => 'Minggu',
        CarbonInterface::MONDAY => 'Senin',
        CarbonInterface::TUESDAY => 'Selasa',
        CarbonInterface::WEDNESDAY => 'Rabu',
        CarbonInterface::THURSDAY => 'Kamis',
        CarbonInterface::FRIDAY => 'Jumat',
        CarbonInterface::SATURDAY => 'Sabtu',
    ];

    protected $table = 'jadwal_pelajarans';

    protected $fillable = [
        'tahun_ajaran_id',
        'kelas_id',
        'mata_pelajaran_id',
        'pengajar_id',
        'hari',
        'waktu_mulai',
        'waktu_selesai',
        'ruang',
        'keterangan',
    ];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class, 'pengajar_id');
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'jadwal_pelajaran_id');
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class, 'jadwal_pelajaran_id');
    }

    public function santris(): HasMany
    {
        return $this->hasMany(Santri::class, 'kelas_id', 'kelas_id');
    }

    public function scopeOrderByHari($query)
    {
        return $query->orderByRaw("CASE hari
            WHEN 'Senin' THEN 1
            WHEN 'Selasa' THEN 2
            WHEN 'Rabu' THEN 3
            WHEN 'Kamis' THEN 4
            WHEN 'Jumat' THEN 5
            WHEN 'Sabtu' THEN 6
            WHEN 'Minggu' THEN 7
            ELSE 8 END");
    }

    /**
     * Mengembalikan nama hari berbahasa Indonesia untuk tanggal tertentu.
     */
    public static function dayNameForDate(\DateTimeInterface|string $date): string
    {
        $parsed = $date instanceof \DateTimeInterface
            ? Carbon::instance($date)
            : Carbon::parse($date);

        return self::DAY_NAMES_BY_WEEKDAY[$parsed->dayOfWeek] ?? 'Minggu';
    }

    /**
     * Memeriksa apakah tanggal sesuai dengan hari jadwal pelajaran ini.
     */
    public function matchesDate(\DateTimeInterface|string $date): bool
    {
        $scheduleDay = self::normalizeDayName($this->hari);
        $targetDay = self::normalizeDayName(self::dayNameForDate($date));

        return $scheduleDay !== '' && $scheduleDay === $targetDay;
    }

    /**
     * Mengembalikan tanggal terbaru (hari ini atau mundur terdekat) yang cocok dengan hari jadwal.
     */
    public function latestMatchingDate(\DateTimeInterface|string|null $referenceDate = null): string
    {
        $cursor = $referenceDate
            ? ($referenceDate instanceof \DateTimeInterface ? Carbon::instance($referenceDate)->startOfDay() : Carbon::parse($referenceDate)->startOfDay())
            : now()->startOfDay();

        for ($offset = 0; $offset < 7; $offset++) {
            $candidate = $cursor->copy()->subDays($offset);
            if ($this->matchesDate($candidate)) {
                return $candidate->toDateString();
            }
        }

        return $cursor->toDateString();
    }

    /**
     * Memvalidasi apakah tanggal absensi diperbolehkan:
     * 1. Format tanggal valid
     * 2. Tidak melebihi tanggal hari ini (tolak tanggal masa depan)
     * 3. Hari pada tanggal tersebut cocok dengan hari jadwal pelajaran
     */
    public function validateAttendanceDate(?string $date): ?string
    {
        if (! filled($date)) {
            return 'Tanggal presensi wajib diisi.';
        }

        try {
            $parsed = Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return 'Format tanggal presensi tidak valid.';
        }

        $today = now()->startOfDay();
        if ($parsed->gt($today)) {
            return 'Tanggal presensi tidak boleh melebihi hari ini (tidak dapat mengisi absensi untuk tanggal masa depan).';
        }

        if (! $this->matchesDate($parsed)) {
            $actualDay = self::dayNameForDate($parsed);

            return "Tanggal {$parsed->format('d/m/Y')} adalah hari {$actualDay}, tidak sesuai dengan jadwal hari {$this->hari}.";
        }

        return null;
    }

    private static function normalizeDayName(?string $day): string
    {
        $normalized = strtolower(trim((string) $day));

        return $normalized === 'ahad' ? 'minggu' : $normalized;
    }
}
