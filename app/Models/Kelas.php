<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelasses';

    protected $fillable = [
        'kode_kelas',
        'nama_kelas',
        'tingkat_kelas',
        'wali_kelas_id',
        'tahun_ajaran_id',
        'educational_unit_id',
        'keterangan',
    ];

    public function educationalUnit(): BelongsTo
    {
        return $this->belongsTo(EducationalUnit::class, 'educational_unit_id');
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class, 'wali_kelas_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function santris(): HasMany
    {
        return $this->hasMany(Santri::class, 'kelas_id');
    }

    public function jadwalPelajarans(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'kelas_id');
    }
}
