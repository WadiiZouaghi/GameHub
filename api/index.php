<?php

$cacheDir = $_ENV['SYMFONY_CACHE_DIR'] ?? '/tmp/cache';
$logDir   = $_ENV['SYMFONY_LOG_DIR'] ?? '/tmp/log';

@mkdir($cacheDir, 0777, true);
@mkdir($logDir, 0777, true);

use App\Kernel;

require_once __DIR__ . '/../vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel(
        $context['APP_ENV'] ?? 'prod',
        (bool) ($context['APP_DEBUG'] ?? false)
    );
};
