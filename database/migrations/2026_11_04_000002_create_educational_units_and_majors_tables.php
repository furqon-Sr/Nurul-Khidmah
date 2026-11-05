<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat tabel educational_units (Multi-Unit Pendidikan: MTs, MA, SMK Otomotif, SMK DKV)
        Schema::create('educational_units', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit', 50)->unique();
            $table->string('nama_unit', 100);
            $table->enum('jenjang', ['MTs', 'MA', 'SMK']);
            $table->foreignId('headmaster_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('letterhead_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Buat tabel majors (Jurusan Kejuruan/Peminatan)
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_unit_id')->constrained('educational_units')->cascadeOnDelete();
            $table->string('kode_jurusan', 50);
            $table->string('nama_jurusan', 100);
            $table->timestamps();
        });

        // 3. Modifikasi tabel kelasses: tambah educational_unit_id
        Schema::table('kelasses', function (Blueprint $table) {
            $table->foreignId('educational_unit_id')
                ->nullable()
                ->after('tahun_ajaran_id')
                ->constrained('educational_units')
                ->nullOnDelete();
        });

        // 4. Modifikasi tabel santris: tambah educational_unit_id
        Schema::table('santris', function (Blueprint $table) {
            $table->foreignId('educational_unit_id')
                ->nullable()
                ->after('kelas_id')
                ->constrained('educational_units')
                ->nullOnDelete();
        });

        // 5. Modifikasi tabel mata_pelajarans: tambah educational_unit_id (nullable)
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->foreignId('educational_unit_id')
                ->nullable()
                ->after('id')
                ->constrained('educational_units')
                ->nullOnDelete();
        });

        // 6. Inisialisasi Data Default Unit Pendidikan & Jurusan
        $mtsId = DB::table('educational_units')->insertGetId([
            'kode_unit' => 'MTS',
            'nama_unit' => 'MTs Nurul Khidmah',
            'jenjang' => 'MTs',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $maId = DB::table('educational_units')->insertGetId([
            'kode_unit' => 'MA',
            'nama_unit' => 'MA Nurul Khidmah',
            'jenjang' => 'MA',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $smkOtoId = DB::table('educational_units')->insertGetId([
            'kode_unit' => 'SMK-OTO',
            'nama_unit' => 'SMK Otomotif Nurul Khidmah',
            'jenjang' => 'SMK',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $smkDkvId = DB::table('educational_units')->insertGetId([
            'kode_unit' => 'SMK-DKV',
            'nama_unit' => 'SMK DKV Nurul Khidmah',
            'jenjang' => 'SMK',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert Jurusan untuk Unit SMK
        DB::table('majors')->insert([
            [
                'educational_unit_id' => $smkOtoId,
                'kode_jurusan' => 'TO',
                'nama_jurusan' => 'Teknik Otomotif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'educational_unit_id' => $smkDkvId,
                'kode_jurusan' => 'DKV',
                'nama_jurusan' => 'Desain Komunikasi Visual',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Hubungkan kelas & santri tingkat 7/8/9 eksisting ke unit MTs
        DB::table('kelasses')->whereNull('educational_unit_id')->update([
            'educational_unit_id' => $mtsId,
        ]);

        DB::table('santris')->whereNull('educational_unit_id')->update([
            'educational_unit_id' => $mtsId,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('educational_unit_id');
        });

        Schema::table('santris', function (Blueprint $table) {
            $table->dropConstrainedForeignId('educational_unit_id');
        });

        Schema::table('kelasses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('educational_unit_id');
        });

        Schema::dropIfExists('majors');
        Schema::dropIfExists('educational_units');
    }
};
