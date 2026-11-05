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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('password');
            }
            $table->string('role', 32)->default('santri')->change();
        });

        // Selaraskan akun role 'admin' lama menjadi 'pengurus_akademik' (Staf TU / Admin Sistem)
        DB::table('users')->where('role', 'admin')->update(['role' => 'pengurus_akademik']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
            $table->string('role', 32)->default('admin')->change();
        });
    }
};
