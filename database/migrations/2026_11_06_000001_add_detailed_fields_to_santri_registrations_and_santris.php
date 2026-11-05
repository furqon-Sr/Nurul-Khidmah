<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom-kolom spesifik PPDB sesuai formulir resmi yayasan:
     * - Bagian 1 (Data Siswa): birth_place, previous_school, nisn, boarding_status
     * - Bagian 2 (Data Wali): father_name, mother_name, guardian_occupation, guardian_address
     * - Bagian 3 (Dokumen Upload): file_kk, file_akta, file_ktp, file_ijazah
     */
    public function up(): void
    {
        Schema::table('santri_registrations', function (Blueprint $table) {
            // Bagian 1: Data Siswa
            $table->string('birth_place')->nullable()->after('student_name');
            $table->string('previous_school')->nullable()->after('residential_address');
            $table->string('nisn', 20)->nullable()->after('previous_school');
            $table->string('boarding_status', 20)->default('Mukim')->after('nisn'); // 'Mukim' atau 'Tidak Mukim'

            // Bagian 2: Data Wali / Orang Tua
            $table->string('father_name')->nullable()->after('guardian_name');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('guardian_occupation')->nullable()->after('mother_name');
            $table->text('guardian_address')->nullable()->after('guardian_occupation');

            // Bagian 3: Dokumen Berkas Upload
            $table->string('file_kk')->nullable()->after('student_photo');
            $table->string('file_akta')->nullable()->after('file_kk');
            $table->string('file_ktp')->nullable()->after('file_akta');
            $table->string('file_ijazah')->nullable()->after('file_ktp');
        });

        Schema::table('santris', function (Blueprint $table) {
            $table->string('nisn', 20)->nullable()->after('nis');
            $table->string('previous_school')->nullable()->after('alamat');
            $table->string('boarding_status', 20)->default('Mukim')->after('previous_school'); // 'Mukim' atau 'Tidak Mukim'
            $table->string('guardian_occupation')->nullable()->after('nama_wali');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $table->dropColumn([
                'nisn',
                'previous_school',
                'boarding_status',
                'guardian_occupation',
            ]);
        });

        Schema::table('santri_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'birth_place',
                'previous_school',
                'nisn',
                'boarding_status',
                'father_name',
                'mother_name',
                'guardian_occupation',
                'guardian_address',
                'file_kk',
                'file_akta',
                'file_ktp',
                'file_ijazah',
            ]);
        });
    }
};
