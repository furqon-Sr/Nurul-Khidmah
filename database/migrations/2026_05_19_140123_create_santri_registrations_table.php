<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('student_name');
            $table->string('gender');
            $table->date('birth_date');
            $table->string('target_class');
            $table->string('branch');
            $table->string('guardian_name');
            $table->string('guardian_email')->nullable();
            $table->string('guardian_phone');
            $table->string('education_qualification');
            $table->text('residential_address');
            $table->string('student_photo')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_registrations');
    }
};
