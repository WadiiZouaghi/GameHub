<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        if (isset($_ENV['SYMFONY_CACHE_DIR'])) {
            return $_ENV['SYMFONY_CACHE_DIR'];
        }

        return parent::getCacheDir();
    }

    public function getBuildDir(): string
    {
        if (isset($_ENV['SYMFONY_CACHE_DIR'])) {
            return $_ENV['SYMFONY_CACHE_DIR'];
        }

        return parent::getBuildDir();
    }

    public function getLogDir(): string
    {
        if (isset($_ENV['SYMFONY_LOG_DIR'])) {
            return $_ENV['SYMFONY_LOG_DIR'];
        }

        return parent::getLogDir();
    }
}
