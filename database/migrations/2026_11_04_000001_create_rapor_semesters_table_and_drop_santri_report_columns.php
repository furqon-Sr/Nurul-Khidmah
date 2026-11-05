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
        // 1. Buat tabel rapor_semesters
        Schema::create('rapor_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelasses')->cascadeOnDelete();
            $table->string('status_kenaikan', 50)->default('Aktif');
            $table->text('catatan_wali_kelas')->nullable();
            $table->string('nilai_sikap', 50)->nullable();
            $table->enum('status_publikasi', ['draft', 'approved', 'published'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['santri_id', 'tahun_ajaran_id'], 'unique_santri_tahun_ajaran_rapor');
        });

        // 2. Salin data catatan_wali_kelas dan nilai_sikap eksisting dari tabel santris ke rapor_semesters
        if (Schema::hasTable('santris') && (Schema::hasColumn('santris', 'nilai_sikap') || Schema::hasColumn('santris', 'catatan_wali_kelas'))) {
            $existingSantris = DB::table('santris')
                ->where(function ($q) {
                    $q->whereNotNull('nilai_sikap')
                      ->orWhereNotNull('catatan_wali_kelas');
                })
                ->get();

            $activeYearId = DB::table('tahun_ajarans')->where('status_aktif', true)->value('id')
                ?: DB::table('tahun_ajarans')->orderByDesc('id')->value('id');

            foreach ($existingSantris as $s) {
                $taId = $activeYearId;
                if ($s->kelas_id) {
                    $kelasTaId = DB::table('kelasses')->where('id', $s->kelas_id)->value('tahun_ajaran_id');
                    if ($kelasTaId) {
                        $taId = $kelasTaId;
                    }
                }

                if ($taId) {
                    DB::table('rapor_semesters')->updateOrInsert(
                        [
                            'santri_id' => $s->id,
                            'tahun_ajaran_id' => $taId,
                        ],
                        [
                            'kelas_id' => $s->kelas_id,
                            'status_kenaikan' => 'Aktif',
                            'catatan_wali_kelas' => $s->catatan_wali_kelas ?? null,
                            'nilai_sikap' => $s->nilai_sikap ?? null,
                            'status_publikasi' => 'published',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            // 3. Hapus kolom nilai_sikap dan catatan_wali_kelas dari tabel santris
            Schema::table('santris', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('santris', 'nilai_sikap')) {
                    $columnsToDrop[] = 'nilai_sikap';
                }
                if (Schema::hasColumn('santris', 'catatan_wali_kelas')) {
                    $columnsToDrop[] = 'catatan_wali_kelas';
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('santris')) {
            Schema::table('santris', function (Blueprint $table) {
                if (!Schema::hasColumn('santris', 'nilai_sikap')) {
                    $table->string('nilai_sikap', 50)->nullable()->after('foto');
                }
                if (!Schema::hasColumn('santris', 'catatan_wali_kelas')) {
                    $table->text('catatan_wali_kelas')->nullable()->after('nilai_sikap');
                }
            });
        }

        Schema::dropIfExists('rapor_semesters');
    }
};
