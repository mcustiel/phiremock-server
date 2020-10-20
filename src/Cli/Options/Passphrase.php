<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

class Passphrase
{
    /** @var string */
    private $pass;

    public function __construct(string $pass)
    {
        $this->pass = $pass;
    }

    public function asString(): string
    {
        return $this->pass;
    }
}
