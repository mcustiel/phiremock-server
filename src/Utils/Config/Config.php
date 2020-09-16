<?php

namespace Mcustiel\Phiremock\Server\Utils\Config;

use Mcustiel\Phiremock\Server\Cli\Options\ExpectationsDirectory;
use Mcustiel\Phiremock\Server\Cli\Options\HostInterface;
use Mcustiel\Phiremock\Server\Cli\Options\PhpFactoryFqcn;
use Mcustiel\Phiremock\Server\Cli\Options\Port;

class Config
{
    /** @var array */
    private $configurationArray;

    public function __construct(array $configurationArray)
    {
        $this->configurationArray = $configurationArray;
    }

    public function getInterfaceIp(): HostInterface
    {
        return new HostInterface($this->configurationArray['ip']);
    }

    public function getPort(): Port
    {
        return new Port((int) $this->configurationArray['port']);
    }

    public function isDebugMode(): bool
    {
        return $this->configurationArray['debug'];
    }

    public function getExpectationsPath(): ExpectationsDirectory
    {
        return new ExpectationsDirectory($this->configurationArray['expectations-dir']);
    }

    public function getFactoryClassName(): PhpFactoryFqcn
    {
        return new PhpFactoryFqcn($this->configurationArray['factory-class']);
    }
}
