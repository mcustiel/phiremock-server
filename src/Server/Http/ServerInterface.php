<?php

namespace Mcustiel\Phiremock\Server\Http;

interface ServerInterface
{
    /**
     * @param RequestHandlerInterface $handler
     */
    public function setRequestHandler(RequestHandlerInterface $handler);
}
