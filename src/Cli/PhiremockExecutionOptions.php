<?php
namespace Mcustiel\Phiremock\Server\Cli;

use Mcustiel\Phiremock\Server\Cli\Options\HostInterface;
use Mcustiel\Phiremock\Server\Cli\Options\Port;
use Mcustiel\Phiremock\Server\Cli\Options\ExpectationsDirectory;
use Mcustiel\Phiremock\Server\Cli\Options\Flag;
use Mcustiel\Phiremock\Server\Model\Implementation\ExpectationAutoStorage;

class PhiremockExecutionOptions
{
    /** @var HostInterface */
    private $interface;
    /** @var Port */
    private $port;
    /** @var ExpectationsDirectory */
    private $expectationsDir;
    /** @var Flag */
    private $debugMode;

    public function __construct(
        HostInterface $interface,
        Port $port,
        ExpectationsDirectory $expectationsDir,
        Flag $debugMode
    ) {
        $this->interface = $interface;
        $this->port = $port;
        $this->expectationsDir = $expectationsDir;
        $this->debugMode = $debugMode;
    }

    /** @return HostInterface */
    public function getInterface()
    {
        return $this->interface;
    }

    /** @return Port */
    public function getPort()
    {
        return $this->port;
    }

    /** @return ExpectationsDirectory */
    public function getExpectationsDirectory()
    {
        return $this->expectationsDir;
    }

    /** @return Flag */
    public function getDebugMode()
    {
        return $this->debugMode;
    }
}
