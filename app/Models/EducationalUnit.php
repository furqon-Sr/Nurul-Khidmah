<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalUnit extends Model
{
    use HasFactory;

    protected $table = 'educational_units';

    protected $fillable = [
        'kode_unit',
        'nama_unit',
        'jenjang',
        'headmaster_id',
        'letterhead_image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function headmaster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'headmaster_id');
    }

    public function majors(): HasMany
    {
        return $this->hasMany(Major::class, 'educational_unit_id');
    }

    public function kelasses(): HasMany
    {
        return $this->hasMany(Kelas::class, 'educational_unit_id');
    }

    public function santris(): HasMany
    {
        return $this->hasMany(Santri::class, 'educational_unit_id');
    }

    public function mataPelajarans(): HasMany
    {
        return $this->hasMany(MataPelajaran::class, 'educational_unit_id');
    }

    public function gradeComponents(): HasMany
    {
        return $this->hasMany(GradeComponent::class, 'educational_unit_id');
    }

    public function gradePredicates(): HasMany
    {
        return $this->hasMany(GradePredicate::class, 'educational_unit_id');
    }
}
