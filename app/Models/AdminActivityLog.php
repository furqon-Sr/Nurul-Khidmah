<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    public $timestamps = false; // Hanya pakai created_at (bukan updated_at)

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'target_type',
        'target_id',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper statis untuk mencatat log aktivitas admin.
     */
    public static function log(
        string $action,
        string $description,
        ?string $targetType = null,
        string|int|null $targetId = null,
    ): self {
        $user = auth()->user();
        $request = request();

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId ? (string) $targetId : null,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'created_at' => now(),
        ]);
    }

    /**
     * Warna badge berdasarkan jenis aksi.
     */
    public function actionColor(): string
    {
        return match ($this->action) {
            'login' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            'login_failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            'logout' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            'create' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            'update' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
            'validate' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
            'approve' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
            'publish' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300',
            'revision' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
            'delete' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            'export' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        };
    }
}
