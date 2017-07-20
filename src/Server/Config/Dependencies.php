<?php

namespace Mcustiel\Phiremock\Server\Config;

class Dependencies
{
    /**
     * @return \Mcustiel\DependencyInjection\DependencyInjectionService
     */
    public static function init()
    {
        return require __DIR__ . '/dependencies-setup.php';
    }
}
