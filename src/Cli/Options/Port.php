<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

class Port
{
    /** @var int */
    private $port;

    public function __construct(int $port)
    {
        $this->ensureIsValidPort($port);
        $this->port = $port;
    }

    public function asInt(): int
    {
        return $this->port;
    }

    /** @throws \InvalidArgumentException */
    private function ensureIsValidPort($port)
    {
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException(sprintf('Invalid port number: %d', $port));
        }
    }
}
