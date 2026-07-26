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
        Schema::create('kelasses', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kelas', 20)->unique();
            $table->string('nama_kelas', 50);
            $table->string('tingkat_kelas', 30);
            $table->foreignId('wali_kelas_id')->nullable()->constrained('pengajars')->nullOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelasses');
    }
};
