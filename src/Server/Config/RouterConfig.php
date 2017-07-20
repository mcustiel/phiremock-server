<?php

namespace Mcustiel\Phiremock\Server\Config;

class RouterConfig
{
    /**
     * @return array
     */
    public static function get()
    {
        return require __DIR__ . '/router-config.php';
    }
}
