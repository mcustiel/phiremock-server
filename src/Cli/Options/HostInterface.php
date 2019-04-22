<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

class HostInterface
{
    /** @var string */
    private $interface;

    /** @param string $interface */
    public function __construct($interface)
    {
        $this->ensureIsString($interface);
        $this->interface = $interface;
    }

    /** @return string */
    public function asString()
    {
        return $this->interface;
    }

    /**
     * @param string $interface
     * @throws \InvalidArgumentException
     */
    private function ensureIsString($interface)
    {
        if (!is_string($interface)) {
            throw new \InvalidArgumentException(
                sprintf('Expected string argument. Got %s', gettype($interface))
            );
        }
    }
}
