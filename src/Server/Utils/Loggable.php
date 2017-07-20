<?php

namespace Mcustiel\Phiremock\Server\Utils;

use Psr\Log\LoggerInterface;

trait Loggable
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
