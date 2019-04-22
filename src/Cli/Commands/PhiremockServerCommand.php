<?php
namespace Mcustiel\Phiremock\Server\Cli\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mcustiel\Phiremock\Server\Cli\Options\HostInterface;
use Mcustiel\Phiremock\Server\Cli\Options\ExpectationsDirectory;
use Mcustiel\Phiremock\Server\Cli\Options\Port;
use Mcustiel\Phiremock\Server\Cli\Options\Flag;
use Mcustiel\Phiremock\Server\Phiremock;

class PhiremockServerCommand extends Command
{
    const IP_HELP_MESSAGE = 'IP address of the interface where Phiremock must list for connections. Default: %s (all interfaces).';
    const DEFAULT_IP = '0.0.0.0';
    const PORT_HELP_MESSAGE = 'IP address of the interface where Phiremock must list for connections. Default: %d.';
    const DEFAULT_PORT = 8086;
    const EXPECTATIONS_DIR_HELP_MESSAGE = 'Directory in which to search for expectation definition files. Default: %s.';
    const DEFAULT_EXPECTATIONS_DIR = '[USER_HOME_PATH]/.phiremock/expectations';
    const DEBUG_HELP_MESSAGE = 'Sets debug mode.';

    /** @var Phiremock */
    private $phiremockApplication;

    public function __construct(Phiremock $phiremockApplication)
    {
        $this->phiremockApplication = $phiremockApplication;
    }

    protected function configure()
    {
        $this->setDescription('Runs Phiremock server')
            ->setHelp('This is the main command to run Phiremock as a HTTP server.');
        $this->addOption(
            'ip',
            'i',
            InputOption::VALUE_REQUIRED,
            sprintf(self::IP_HELP_MESSAGE, self::DEFAULT_IP),
            self::DEFAULT_IP
        );
        $this->addOption(
            'port',
            'p',
            InputOption::VALUE_REQUIRED,
            sprintf(self::PORT_HELP_MESSAGE, self::DEFAULT_PORT),
            self::DEFAULT_PORT
        );
        $this->addOption(
            'expectations-dir',
            'e',
            InputOption::VALUE_REQUIRED,
            sprintf(self::EXPECTATIONS_DIR_HELP_MESSAGE, self::DEFAULT_EXPECTATIONS_DIR),
            self::DEFAULT_EXPECTATIONS_DIR
        );
        $this->addOption(
            'debug',
            'd',
            InputOption::VALUE_NONE,
            sprintf(self::DEBUG_HELP_MESSAGE),
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ip = $input->getOption('ip');
        $port = (int) $input->getOption('port');
        $expectationsDir = $input->getOption('expectations-dir');
        $debugMode = $input->hasOption('debug');

        $executionOptions = new PhiremockExecutionOptions(
            new HostInterface($ip),
            new Port($port),
            new ExpectationsDirectory($expectationsDir),
            new Flag($debugMode)
        );
        $this->phiremockApplication->run($executionOptions);
    }
}
