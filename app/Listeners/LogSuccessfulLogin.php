<?php

namespace App\Listeners;

use App\Models\AdminActivityLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        AdminActivityLog::log(
            'login',
            'Login ke panel admin',
            'User',
            $event->user->id,
        );
    }
}
