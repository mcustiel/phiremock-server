<?php

namespace Mcustiel\Phiremock\Server\Http\InputSources;

use Psr\Http\Message\ServerRequestInterface;

class Body implements InputSourceInterface
{
    public function getValue(ServerRequestInterface $request, $argument = null): ?string
    {
        var_dump($request->getBody()->__toString());

        return $request->getBody()->__toString();
    }
}
