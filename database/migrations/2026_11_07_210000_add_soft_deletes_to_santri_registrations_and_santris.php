<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('santri_registrations') && ! Schema::hasColumn('santri_registrations', 'deleted_at')) {
            Schema::table('santri_registrations', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('santris') && ! Schema::hasColumn('santris', 'deleted_at')) {
            Schema::table('santris', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('santri_registrations') && Schema::hasColumn('santri_registrations', 'deleted_at')) {
            Schema::table('santri_registrations', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('santris') && Schema::hasColumn('santris', 'deleted_at')) {
            Schema::table('santris', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
