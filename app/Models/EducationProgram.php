<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EducationProgram extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'is_active',
        'is_registration_open',
        'is_coming_soon',
        'order',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'is_registration_open' => 'boolean',
        'is_coming_soon'       => 'boolean',
    ];

    /**
     * Auto-generate slug from name if not provided.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($program) {
            if (empty($program->slug)) {
                $program->slug = Str::slug($program->name);
            }
        });

        static::updating(function ($program) {
            if ($program->isDirty('name') && ! $program->isDirty('slug')) {
                $program->slug = Str::slug($program->name);
            }
        });
    }

    /**
     * Scope: only active programs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
