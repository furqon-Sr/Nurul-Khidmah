<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapor_semesters', function (Blueprint $table) {
            if (! Schema::hasColumn('rapor_semesters', 'workflow_stage')) {
                $table->string('workflow_stage', 30)->default('draft')->after('status_publikasi');
            }
            if (! Schema::hasColumn('rapor_semesters', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('workflow_stage');
            }
            if (! Schema::hasColumn('rapor_semesters', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('version')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('rapor_semesters', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by');
            }
            if (! Schema::hasColumn('rapor_semesters', 'published_by')) {
                $table->foreignId('published_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('rapor_semesters', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('published_by');
            }
            if (! Schema::hasColumn('rapor_semesters', 'revision_note')) {
                $table->text('revision_note')->nullable()->after('published_at');
            }
            if (! Schema::hasColumn('rapor_semesters', 'revision_requested_by')) {
                $table->foreignId('revision_requested_by')->nullable()->after('revision_note')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('rapor_semesters', 'revision_requested_at')) {
                $table->timestamp('revision_requested_at')->nullable()->after('revision_requested_by');
            }
        });

        // Sinkronisasi baris eksisting agar status alur sesuai rekam persetujuan sebelumnya
        DB::table('rapor_semesters')
            ->where('status_publikasi', 'approved')
            ->update(['workflow_stage' => 'approved']);

        DB::table('rapor_semesters')
            ->where('status_publikasi', 'published')
            ->whereNotNull('approved_at')
            ->update(['workflow_stage' => 'published']);
    }

    public function down(): void
    {
        Schema::table('rapor_semesters', function (Blueprint $table) {
            foreach (['validated_by', 'published_by', 'revision_requested_by'] as $fkCol) {
                if (Schema::hasColumn('rapor_semesters', $fkCol)) {
                    $table->dropConstrainedForeignId($fkCol);
                }
            }

            $cols = array_filter([
                Schema::hasColumn('rapor_semesters', 'workflow_stage') ? 'workflow_stage' : null,
                Schema::hasColumn('rapor_semesters', 'version') ? 'version' : null,
                Schema::hasColumn('rapor_semesters', 'validated_at') ? 'validated_at' : null,
                Schema::hasColumn('rapor_semesters', 'published_at') ? 'published_at' : null,
                Schema::hasColumn('rapor_semesters', 'revision_note') ? 'revision_note' : null,
                Schema::hasColumn('rapor_semesters', 'revision_requested_at') ? 'revision_requested_at' : null,
            ]);

            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
