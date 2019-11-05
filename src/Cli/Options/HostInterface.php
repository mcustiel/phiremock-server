<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

class HostInterface
{
    /** @var string */
    private $interface;

    public function __construct(string $interface)
    {
        $this->interface = $interface;
    }

    public function asString(): string
    {
        return $this->interface;
    }
}
