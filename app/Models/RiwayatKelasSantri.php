<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKelasSantri extends Model
{
    protected $table = 'riwayat_kelas_santris';

    protected $fillable = [
        'santri_id',
        'kelas_lama_id',
        'kelas_baru_id',
        'tahun_ajaran_id',
        'jenis_aksi',
        'catatan',
        'created_by',
    ];

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function kelasLama(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_lama_id');
    }

    public function kelasBaru(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_baru_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
