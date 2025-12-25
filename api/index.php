<?php

require_once __DIR__ . '/../vendor/autoload_runtime.php';

use Symfony\Component\Filesystem\Filesystem;
use App\Kernel;

$cacheDir = $_ENV['SYMFONY_CACHE_DIR'] ?? '/tmp/cache';
$logDir   = $_ENV['SYMFONY_LOG_DIR'] ?? '/tmp/log';

$_SERVER['SYMFONY_CACHE_DIR'] = $_ENV['SYMFONY_CACHE_DIR'] = $cacheDir;
$_SERVER['SYMFONY_LOG_DIR']   = $_ENV['SYMFONY_LOG_DIR']   = $logDir;

$filesystem = new Filesystem();
if (!$filesystem->exists($cacheDir)) {
    $filesystem->mkdir($cacheDir, 0777);
}
if (!$filesystem->exists($logDir)) {
    $filesystem->mkdir($logDir, 0777);
}

return function (array $context) {
    return new Kernel(
        $context['APP_ENV'] ?? 'prod',
        (bool) ($context['APP_DEBUG'] ?? false)
    );
};
