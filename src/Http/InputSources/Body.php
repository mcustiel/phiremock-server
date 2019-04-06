<?php

namespace Mcustiel\Phiremock\Server\Http\InputSources;

use Psr\Http\Message\ServerRequestInterface;

class Body implements InputSourceInterface
{
    public function getValue(ServerRequestInterface $request, $argument = null)
    {
        return $request->getBody()->__toString();
    }
}
