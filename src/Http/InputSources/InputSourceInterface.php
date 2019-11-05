<?php

namespace Mcustiel\Phiremock\Server\Http\InputSources;

use Psr\Http\Message\ServerRequestInterface;

interface InputSourceInterface
{
    /**
     * @param mixed $argument
     *
     * @return : ?string
     */
    public function getValue(ServerRequestInterface $request, $argument = null): ?string;
}
