<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_unit_id')->nullable()->constrained('educational_units')->cascadeOnDelete();
            $table->string('nama_komponen', 100);
            $table->decimal('bobot_persen', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('grade_predicates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_unit_id')->nullable()->constrained('educational_units')->cascadeOnDelete();
            $table->string('huruf', 10);
            $table->decimal('batas_bawah', 5, 2);
            $table->decimal('batas_atas', 5, 2)->default(100);
            $table->timestamps();
        });

        Schema::table('absensis', function (Blueprint $table) {
            $table->string('status_kehadiran', 20)->default('Hadir')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_predicates');
        Schema::dropIfExists('grade_components');

        Schema::table('absensis', function (Blueprint $table) {
            $table->string('status_kehadiran', 20)->default('Hadir')->change();
        });
    }
};
