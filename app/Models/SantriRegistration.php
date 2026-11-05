<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SantriRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'student_name',
        'gender',
        'birth_date',
        'target_class',
        'academic_year',
        'branch',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'education_qualification',
        'residential_address',
        'student_photo',
        'status',
        'santri_id',
    ];

    public function santri(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
