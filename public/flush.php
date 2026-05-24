<?php
/**
 * One-time cache flush script.
 * Access via: http://nurulkhidmah.test/flush.php
 * DELETE this file after use.
 */
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clear the problematic cache entries
\Illuminate\Support\Facades\Cache::forget('site_global_data');
\Illuminate\Support\Facades\Cache::forget('site_settings_cache');

echo '<h2 style="font-family:sans-serif;color:green;">✅ Cache berhasil dibersihkan!</h2>';
echo '<p style="font-family:sans-serif;">Silakan <a href="/">kunjungi homepage</a> untuk mengecek hasilnya.</p>';
echo '<p style="font-family:sans-serif;color:red;"><strong>Hapus file flush.php ini setelah selesai.</strong></p>';
