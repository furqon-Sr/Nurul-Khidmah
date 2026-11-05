<?php

namespace App\Listeners;

use App\Models\AdminActivityLog;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        try {
            $email = is_array($event->credentials) ? ($event->credentials['email'] ?? '-') : '-';

            AdminActivityLog::create([
                'user_id' => $event->user?->id,
                'user_name' => $event->user?->name ?? (string) $email,
                'action' => 'login_failed',
                'target_type' => 'Auth',
                'description' => "Percobaan login gagal pada portal staf/sistem untuk identitas: {$email}",
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Abaikan jika tabel belum dimigrasikan pada saat bootstrap awal
        }
    }
}
