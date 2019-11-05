<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ActionInterface
{
    /** @return ResponseInterface */
    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
