<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('riwayat_kelas_santris')) {
            Schema::create('riwayat_kelas_santris', function (Blueprint $table) {
                $table->id();
                $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
                $table->foreignId('kelas_lama_id')->nullable()->constrained('kelasses')->nullOnDelete();
                $table->foreignId('kelas_baru_id')->nullable()->constrained('kelasses')->nullOnDelete();
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();
                $table->string('jenis_aksi', 30); // Naik Kelas, Tinggal Kelas, Pindah Kelas, Lulus
                $table->string('catatan', 255)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['santri_id', 'tahun_ajaran_id']);
            });
        }

        // Normalisasi cabang objek penelitian hanya ke Pondok Pesantren Nurul Khidmah Lamongan
        if (Schema::hasTable('santri_registrations')) {
            DB::table('santri_registrations')
                ->where('branch', 'like', '%Surabaya%')
                ->update(['branch' => 'Lamongan']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas_santris');
    }
};
