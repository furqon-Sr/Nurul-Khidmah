<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alur import bertahap (unggah → validasi → pratinjau → import):
     * hasil validasi disimpan pada batch sebelum data masuk ke tabel utama.
     */
    public function up(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->unsignedInteger('valid_rows')->default(0)->after('processed_rows');
            $table->unsignedInteger('invalid_rows')->default(0)->after('valid_rows');
            $table->unsignedInteger('duplicate_rows')->default(0)->after('invalid_rows');
            $table->unsignedInteger('skipped_rows')->default(0)->after('duplicate_rows');
            $table->string('mode', 20)->nullable()->after('skipped_rows');
            $table->json('report')->nullable()->after('errors');
        });
    }

    public function down(): void
    {
        Schema::table('import_batches', function (Blueprint $table) {
            $table->dropColumn(['valid_rows', 'invalid_rows', 'duplicate_rows', 'skipped_rows', 'mode', 'report']);
        });
    }
};
