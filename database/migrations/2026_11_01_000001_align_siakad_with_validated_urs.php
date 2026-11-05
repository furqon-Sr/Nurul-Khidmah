<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penyelarasan skema SIAKAD & PPDB sesuai Dokumen URS Tervalidasi (14 Responden):
     * 1. santri_registrations: relasi santri_id (idempotensi konversi akun Wali & NIS otomatis - FR-02)
     * 2. santris: dukungan status Aktif/Lulus/Alumni/Mutasi/Non-Aktif (FR-03), kontak WA wali, pasfoto,
     *    serta penilaian sikap/kepesantrenan & catatan wali kelas pada rapor (FR-10 & FR-12)
     * 3. kelasses: kode_kelas unik per tahun_ajaran_id, bukan unik global lintas tahun (FR-06)
     */
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $table->string('nama_wali', 255)->nullable()->change();
            $table->string('status_santri', 30)->default('Aktif')->change();
            $table->string('no_hp_wali', 30)->nullable()->after('nama_wali');
            $table->string('foto')->nullable()->after('status_santri');
            $table->string('nilai_sikap', 50)->nullable()->after('foto');
            $table->text('catatan_wali_kelas')->nullable()->after('nilai_sikap');
        });

        Schema::table('santri_registrations', function (Blueprint $table) {
            $table->foreignId('santri_id')
                ->nullable()
                ->after('status')
                ->constrained('santris')
                ->nullOnDelete();
        });

        Schema::table('kelasses', function (Blueprint $table) {
            $table->dropUnique(['kode_kelas']);
            $table->unique(['kode_kelas', 'tahun_ajaran_id'], 'unique_kode_kelas_per_ta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelasses', function (Blueprint $table) {
            $table->dropUnique('unique_kode_kelas_per_ta');
            $table->unique('kode_kelas');
        });

        Schema::table('santri_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('santri_id');
        });

        Schema::table('santris', function (Blueprint $table) {
            $table->dropColumn(['no_hp_wali', 'foto', 'nilai_sikap', 'catatan_wali_kelas']);
        });
    }
};
