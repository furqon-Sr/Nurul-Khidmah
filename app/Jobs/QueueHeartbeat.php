<?php

namespace App\Jobs;

use App\Services\QueueHealth;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Job ringan untuk membuktikan bahwa worker antrean benar-benar berjalan:
 * hanya worker yang aktif yang dapat memprosesnya dan mencatat detak (heartbeat).
 */
class QueueHeartbeat implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function handle(): void
    {
        QueueHealth::recordHeartbeat();
    }
}
