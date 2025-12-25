<?php

use Symfony\Component\Filesystem\Filesystem;

$fs = new Filesystem();
$fs->mkdir($_ENV['SYMFONY_CACHE_DIR'] ?? '/tmp/cache', 0777);
$fs->mkdir($_ENV['SYMFONY_LOG_DIR'] ?? '/tmp/log', 0777);

use App\Kernel;

require_once __DIR__ . '/../vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
