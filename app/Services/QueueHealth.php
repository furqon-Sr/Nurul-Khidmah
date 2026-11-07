<?php

namespace App\Services;

use App\Jobs\QueueHeartbeat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pemeriksaan kesehatan layanan antrean (worker) yang sebenarnya.
 *
 * Konfigurasi driver `database` tidak membuktikan worker berjalan. Karena itu status berasal dari:
 *  1. detak (heartbeat) yang dicatat oleh job probe saat worker memprosesnya, dan
 *  2. job lama yang menumpuk tanpa diproses.
 */
class QueueHealth
{
    public const HEARTBEAT_KEY = 'import-queue:heartbeat';

    public const PROBE_KEY = 'import-queue:probe-at';

    /** Detak dianggap segar selama jendela ini (detik). */
    public const FRESH_SECONDS = 180;

    /** Batas tunggu hasil probe sebelum layanan dianggap tidak tersedia (detik). */
    public const PROBE_TIMEOUT = 45;

    public static function recordHeartbeat(): void
    {
        Cache::put(self::HEARTBEAT_KEY, now()->timestamp, now()->addHours(6));
    }

    /** Kirim probe bila detak sudah usang dan belum ada probe yang sedang menunggu. */
    public static function probe(): void
    {
        if (config('queue.default') === 'sync') {
            return;
        }

        $beat = (int) Cache::get(self::HEARTBEAT_KEY, 0);
        $probeAt = (int) Cache::get(self::PROBE_KEY, 0);
        $now = now()->timestamp;

        $stale = $now - $beat > self::FRESH_SECONDS / 2;
        $probeExpired = $now - $probeAt > self::PROBE_TIMEOUT * 2;

        if ($stale && $probeExpired) {
            Cache::put(self::PROBE_KEY, $now, now()->addHour());
            QueueHeartbeat::dispatch();
        }
    }

    /**
     * @return array{state:'active'|'unavailable'|'unknown', label:string, hint:string, last_seen:?string, detail:array<string,string>}
     */
    public static function status(): array
    {
        $connection = (string) config('queue.default');
        $detail = ['Driver' => $connection, 'Antrean' => (string) config("queue.connections.{$connection}.queue", 'default')];

        if ($connection === 'sync') {
            return self::result('active', 'Layanan import aktif', 'Import diproses langsung tanpa antrean.', null, $detail);
        }

        $now = now()->timestamp;
        $beat = (int) Cache::get(self::HEARTBEAT_KEY, 0);
        $probeAt = (int) Cache::get(self::PROBE_KEY, 0);
        $lastSeen = $beat ? now()->setTimestamp($beat)->diffForHumans() : null;
        $detail['Detak terakhir'] = $beat ? now()->setTimestamp($beat)->format('d M Y H:i:s') : 'Belum ada';

        if ($beat && $now - $beat <= self::FRESH_SECONDS) {
            return self::result('active', 'Layanan import aktif', 'Worker antrean merespons.', $lastSeen, $detail);
        }

        $stuck = self::hasStuckJobs();
        $probeTimedOut = $probeAt && ($now - $probeAt > self::PROBE_TIMEOUT);

        if ($stuck || $probeTimedOut) {
            return self::result('unavailable', 'Layanan import tidak tersedia', 'Worker antrean tidak merespons. Import akan menunggu sampai layanan dijalankan kembali.', $lastSeen, $detail);
        }

        return self::result('unknown', 'Status belum diketahui', 'Sedang memeriksa layanan antrean…', $lastSeen, $detail);
    }

    private static function hasStuckJobs(): bool
    {
        if (config('queue.default') !== 'database' || ! Schema::hasTable(config('queue.connections.database.table', 'jobs'))) {
            return false;
        }

        return DB::table(config('queue.connections.database.table', 'jobs'))
            ->where('created_at', '<', now()->subSeconds(self::PROBE_TIMEOUT * 2)->timestamp)
            ->exists();
    }

    private static function result(string $state, string $label, string $hint, ?string $lastSeen, array $detail): array
    {
        return ['state' => $state, 'label' => $label, 'hint' => $hint, 'last_seen' => $lastSeen, 'detail' => $detail];
    }
}
