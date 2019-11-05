<?php

namespace Mcustiel\Phiremock\Server\Http\InputSources;

use Psr\Http\Message\ServerRequestInterface;

class Header implements InputSourceInterface
{
    public function getValue(ServerRequestInterface $request, $argument = null): ?string
    {
        $header = $request->getHeaderLine($argument);

        return $header ?: null;
    }
}
