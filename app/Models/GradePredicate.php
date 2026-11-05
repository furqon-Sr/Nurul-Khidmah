<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradePredicate extends Model
{
    use HasFactory;

    protected $table = 'grade_predicates';

    protected $fillable = [
        'educational_unit_id',
        'huruf',
        'batas_bawah',
        'batas_atas',
    ];

    protected $casts = [
        'batas_bawah' => 'float',
        'batas_atas' => 'float',
    ];

    protected static array $unitCache = [];

    public function educationalUnit(): BelongsTo
    {
        return $this->belongsTo(EducationalUnit::class, 'educational_unit_id');
    }

    /**
     * Mengambil daftar predikat nilai terurut dari batas bawah tertinggi.
     */
    public static function getPredicatesForUnit(?int $educationalUnitId = null): Collection
    {
        $cacheKey = $educationalUnitId ?? 0;
        if (isset(static::$unitCache[$cacheKey])) {
            return static::$unitCache[$cacheKey];
        }

        if ($educationalUnitId) {
            $unitPredicates = static::where('educational_unit_id', $educationalUnitId)
                ->orderByDesc('batas_bawah')
                ->get();
            if ($unitPredicates->isNotEmpty()) {
                return static::$unitCache[$cacheKey] = $unitPredicates;
            }
        }

        $globalPredicates = static::whereNull('educational_unit_id')
            ->orderByDesc('batas_bawah')
            ->get();
        if ($globalPredicates->isNotEmpty()) {
            return static::$unitCache[$cacheKey] = $globalPredicates;
        }

        return static::$unitCache[$cacheKey] = static::orderByDesc('batas_bawah')->get();
    }

    public static function flushUnitCache(): void
    {
        static::$unitCache = [];
    }
}
