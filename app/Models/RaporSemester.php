<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaporSemester extends Model
{
    use HasFactory;

    protected $table = 'rapor_semesters';

    protected $fillable = [
        'santri_id',
        'tahun_ajaran_id',
        'kelas_id',
        'status_kenaikan',
        'catatan_wali_kelas',
        'nilai_sikap',
        'status_publikasi',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status_publikasi === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status_publikasi === 'approved';
    }

    public function isPublished(): bool
    {
        return $this->status_publikasi === 'published';
    }
}
