<?php

namespace Mcustiel\Phiremock\Server\Http\InputSources;

use Psr\Http\Message\ServerRequestInterface;

interface InputSourceInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param mixed                                    $argument
     *
     * @return mixed
     */
    public function getValue(ServerRequestInterface $request, $argument = null);
}
