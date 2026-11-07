<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    use HasFactory;

    /** Hasil validasi tersedia, menunggu konfirmasi pengguna (belum ada data masuk ke tabel utama). */
    public const STATUS_VALIDATED = 'validated';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_SANTRI = 'santri';

    public const TYPE_PENGAJAR = 'pengajar';

    public const MODE_SKIP_EXISTING = 'skip';

    public const MODE_UPDATE_EXISTING = 'update';

    protected $fillable = [
        'file_name',
        'file_path',
        'type',
        'total_rows',
        'processed_rows',
        'valid_rows',
        'invalid_rows',
        'duplicate_rows',
        'skipped_rows',
        'mode',
        'status',
        'errors',
        'report',
        'created_by',
    ];

    protected $casts = [
        'errors' => 'array',
        'report' => 'array',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'valid_rows' => 'integer',
        'invalid_rows' => 'integer',
        'duplicate_rows' => 'integer',
        'skipped_rows' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Batch yang masih berjalan di antrean (perlu auto-refresh pada tampilan). */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    public function isAwaitingConfirmation(): bool
    {
        return $this->status === self::STATUS_VALIDATED;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_PENGAJAR ? 'Pengajar' : 'Santri';
    }

    public function getStatusChipClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'chip-success',
            self::STATUS_COMPLETED_WITH_ERRORS => 'chip-warning',
            self::STATUS_PROCESSING => 'chip-info',
            self::STATUS_FAILED => 'chip-error',
            self::STATUS_VALIDATED => 'chip-purple',
            self::STATUS_CANCELLED => 'chip-muted',
            default => 'chip-neutral',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_VALIDATED => 'Menunggu konfirmasi',
            self::STATUS_PROCESSING => 'Memproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_COMPLETED_WITH_ERRORS => 'Selesai dengan kesalahan',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Menunggu',
        };
    }

    /** Persentase progres pemrosesan antrean (0–100). */
    public function getProgressPercentAttribute(): int
    {
        if ($this->total_rows <= 0) {
            return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_COMPLETED_WITH_ERRORS], true) ? 100 : 0;
        }

        return (int) min(100, round($this->processed_rows / $this->total_rows * 100));
    }

    /** Data ringkas untuk polling status di halaman riwayat. */
    public function toStatusPayload(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'chip' => $this->status_chip_class,
            'processed' => $this->processed_rows,
            'total' => $this->total_rows,
            'percent' => $this->progress_percent,
            'invalid' => $this->invalid_rows,
            'skipped' => $this->skipped_rows,
            'active' => $this->isActive(),
        ];
    }
}
