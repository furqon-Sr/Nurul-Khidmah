<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilais';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'santri_id',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'grade',
        'keterangan',
        'created_by',
    ];

    public function jadwalPelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mataPelajaran(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            MataPelajaran::class,
            JadwalPelajaran::class,
            'id',
            'id',
            'jadwal_pelajaran_id',
            'mata_pelajaran_id'
        );
    }

    public function gradeComponent(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(
            GradeComponent::class,
            Santri::class,
            'id',
            'educational_unit_id',
            'santri_id',
            'educational_unit_id'
        );
    }

    public static function calculateGrade(float $nilaiAkhir, ?int $educationalUnitId = null): string
    {
        $predicates = GradePredicate::getPredicatesForUnit($educationalUnitId);

        if ($predicates->isNotEmpty()) {
            foreach ($predicates as $predicate) {
                $min = (float) $predicate->batas_bawah;
                $max = $predicate->batas_atas !== null ? (float) $predicate->batas_atas : 100.0;
                if ($nilaiAkhir >= $min && $nilaiAkhir <= $max) {
                    return $predicate->huruf;
                }
            }
        }

        if ($nilaiAkhir >= 85) {
            return 'A';
        }
        if ($nilaiAkhir >= 75) {
            return 'B';
        }
        if ($nilaiAkhir >= 65) {
            return 'C';
        }
        if ($nilaiAkhir >= 50) {
            return 'D';
        }

        return 'E';
    }
}
