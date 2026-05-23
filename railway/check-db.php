<?php

declare(strict_types=1);

/**
 * Проверка подключения к БД (для pre-deploy, без migrate:status).
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "ok\n";
    exit(0);
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit(1);
}
