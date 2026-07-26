<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name');         // Simpan nama agar tetap ada meski user dihapus
            $table->string('action', 50);        // login, logout, create, update, delete, export, etc.
            $table->string('target_type', 100)->nullable(); // Model/entity yang diubah (User, News, dll.)
            $table->string('target_id', 50)->nullable();    // ID dari entity yang diubah
            $table->string('description');       // Deskripsi singkat aksi
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
