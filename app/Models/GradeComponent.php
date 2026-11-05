<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeComponent extends Model
{
    use HasFactory;

    protected $table = 'grade_components';

    protected $fillable = [
        'educational_unit_id',
        'nama_komponen',
        'bobot_persen',
    ];

    protected $casts = [
        'bobot_persen' => 'float',
    ];

    protected static array $unitCache = [];

    public function educationalUnit(): BelongsTo
    {
        return $this->belongsTo(EducationalUnit::class, 'educational_unit_id');
    }

    /**
     * Mengambil komponen penilaian untuk unit pendidikan tertentu.
     * Jika unit belum memiliki konfigurasi khusus, ambil default global (educational_unit_id null).
     */
    public static function getComponentsForUnit(?int $educationalUnitId = null): Collection
    {
        $cacheKey = $educationalUnitId ?? 0;
        if (isset(static::$unitCache[$cacheKey])) {
            return static::$unitCache[$cacheKey];
        }

        if ($educationalUnitId) {
            $unitComponents = static::where('educational_unit_id', $educationalUnitId)->get();
            if ($unitComponents->isNotEmpty()) {
                return static::$unitCache[$cacheKey] = $unitComponents;
            }
        }

        $globalComponents = static::whereNull('educational_unit_id')->get();
        if ($globalComponents->isNotEmpty()) {
            return static::$unitCache[$cacheKey] = $globalComponents;
        }

        return static::$unitCache[$cacheKey] = static::all();
    }

    public static function flushUnitCache(): void
    {
        static::$unitCache = [];
    }
}
