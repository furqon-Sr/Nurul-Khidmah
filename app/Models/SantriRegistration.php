<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SantriRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_number',
        'student_name',
        'birth_place',
        'gender',
        'birth_date',
        'target_class',
        'academic_year',
        'branch',
        'guardian_name',
        'father_name',
        'mother_name',
        'guardian_occupation',
        'guardian_address',
        'guardian_email',
        'guardian_phone',
        'education_qualification',
        'residential_address',
        'previous_school',
        'nisn',
        'boarding_status',
        'student_photo',
        'file_kk',
        'file_akta',
        'file_ktp',
        'file_ijazah',
        'status',
        'santri_id',
    ];

    public function santri(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
}
