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
        'branch',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'education_qualification',
        'residential_address',
        'student_photo',
        'status',
    ];
}
