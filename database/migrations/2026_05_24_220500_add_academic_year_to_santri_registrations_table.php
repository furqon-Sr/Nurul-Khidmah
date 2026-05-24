<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri_registrations', function (Blueprint $table) {
            $table->string('academic_year')->default('2026/2027')->after('target_class');
        });
    }

    public function down(): void
    {
        Schema::table('santri_registrations', function (Blueprint $table) {
            $table->dropColumn('academic_year');
        });
    }
};
