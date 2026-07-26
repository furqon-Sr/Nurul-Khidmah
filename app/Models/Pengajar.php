<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengajar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomor_identitas',
        'nama_lengkap',
        'jenis_kelamin',
        'alamat',
        'nomor_telepon',
        'status_pengajar',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelasWali(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }
}
