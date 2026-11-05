<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalPelajaran extends Model
{
    use HasFactory;

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
}
