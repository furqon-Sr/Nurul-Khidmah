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
        'workflow_stage',
        'version',
        'validated_by',
        'validated_at',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'revision_note',
        'revision_requested_by',
        'revision_requested_at',
    ];

    protected $casts = [
        'version' => 'integer',
        'validated_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'revision_requested_at' => 'datetime',
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

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function revisionRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revision_requested_by');
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

    public function isFinalLocked(): bool
    {
        if ($this->status_publikasi === 'approved') {
            return true;
        }

        if ($this->status_publikasi === 'published') {
            return in_array($this->workflow_stage, ['approved', 'published'], true)
                || $this->approved_at !== null
                || $this->published_at !== null;
        }

        return false;
    }
}
