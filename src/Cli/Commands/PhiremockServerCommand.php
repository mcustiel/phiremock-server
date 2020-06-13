<?php
/**
 * This file is part of Phiremock.
 *
 * Phiremock is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Phiremock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Phiremock.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mcustiel\Phiremock\Server\Cli\Commands;

use Mcustiel\Phiremock\Server\Cli\Options\ExpectationsDirectory;
use Mcustiel\Phiremock\Server\Cli\Options\HostInterface;
use Mcustiel\Phiremock\Server\Cli\Options\Port;
use Mcustiel\Phiremock\Server\Factory\Factory;
use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PhiremockServerCommand extends Command
{
    const IP_HELP_MESSAGE = 'IP address of the interface where Phiremock must list for connections.';
    const DEFAULT_IP = '0.0.0.0';
    const PORT_HELP_MESSAGE = 'Port where Phiremock must list for connections.';
    const DEFAULT_PORT = 8086;
    const EXPECTATIONS_DIR_HELP_MESSAGE = 'Directory in which to search for expectation definition files.';
    const DEFAULT_EXPECTATIONS_DIR = '[USER_HOME_PATH]/.phiremock/expectations';
    const DEBUG_HELP_MESSAGE = 'Sets debug mode.';

    /** @var Factory */
    private $factory;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
        parent::__construct('run');
    }

    protected function configure(): void
    {
        $this->setDescription('Runs Phiremock server')
            ->setHelp('This is the main command to run Phiremock as a HTTP server.');
        $this->addOption(
            'ip',
            'i',
            InputOption::VALUE_REQUIRED,
            self::IP_HELP_MESSAGE,
            self::DEFAULT_IP
        );
        $this->addOption(
            'port',
            'p',
            InputOption::VALUE_REQUIRED,
            self::PORT_HELP_MESSAGE,
            self::DEFAULT_PORT
        );
        $this->addOption(
            'expectations-dir',
            'e',
            InputOption::VALUE_REQUIRED,
            sprintf(self::EXPECTATIONS_DIR_HELP_MESSAGE, self::DEFAULT_EXPECTATIONS_DIR),
            null
        );
        $this->addOption(
            'debug',
            'd',
            InputOption::VALUE_NONE,
            sprintf(self::DEBUG_HELP_MESSAGE)
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->startProcess($input);
        $this->processFileExpectations($input);
        $this->startHttpServer($input);

        return 0;
    }

    private function startHttpServer(InputInterface $input): void
    {
        $interface = new HostInterface($input->getOption('ip'));
        $port = new Port((int) $input->getOption('port'));
        $httpServer = $this->factory->createHttpServer();
        $this->setUpHandlers($httpServer);
        $httpServer->listen($interface, $port);
    }

    private function startProcess($input): void
    {
        \define('IS_DEBUG_MODE', $input->hasOption('debug'));

        $this->logger = $this->factory->createLogger();
        $this->logger->info(
            sprintf(
                '[%s] Starting Phiremock %s...',
                date('Y-m-d H:i:s'),
                (IS_DEBUG_MODE ? ' in debug mode' : '')
            )
        );
    }

    private function processFileExpectations(InputInterface $input): void
    {
        $expectationsDir = $this->getExpectationsDir($input)->asString();
        $this->logger->debug("Phiremock's expectation dir is set to: {$expectationsDir}");
        if (is_dir($expectationsDir)) {
            $this->factory
                ->createFileExpectationsLoader()
                ->loadExpectationsFromDirectory($expectationsDir);
        } else {
            $this->logger->debug(
                sprintf(
                    'Not loading expectations file because %s directory does not exist',
                    $expectationsDir
                )
            );
        }
    }

    private function getExpectationsDir(InputInterface $input): ExpectationsDirectory
    {
        if ($input->getOption('expectations-dir')) {
            return new ExpectationsDirectory(
                $this->factory
                    ->createFileSystemService()
                    ->getRealPath($input->getOption('expectations-dir'))
            );
        }

        return new ExpectationsDirectory(
            $this->factory->createHomePathService()->getHomePath()
            . \DIRECTORY_SEPARATOR
            . '.phiremock'
            . \DIRECTORY_SEPARATOR
            . 'expectations'
        );
    }

    private function setUpHandlers(ServerInterface $server): void
    {
        $handleTermination = function ($signal = 0) use ($server) {
            $this->logger->info('Stopping Phiremock...');
            $server->shutdown();
            $this->logger->info('Bye bye');
        };

        $this->logger->debug('Registering shutdown function');
        register_shutdown_function($handleTermination);

        if (\function_exists('pcntl_signal')) {
            $this->logger->debug('PCNTL present: Installing signal handlers');
            pcntl_signal(SIGTERM, $handleTermination);
            pcntl_signal(SIGABRT, $handleTermination);
            pcntl_signal(SIGINT, $handleTermination);
        }

        $errorHandler = function ($severity, $message, $file, $line, $context = null) {
            $errorInformation = sprintf('%s:%s (%s)', $file, $line, $message);
            if ($this->isError($severity)) {
                $this->logger->error($errorInformation);
                throw new \ErrorException($message, 0, $severity, $file, $line);
            }
            $this->logger->warning($errorInformation);

            return false;
        };
        set_error_handler($errorHandler);
    }

    private function isError(int $severity): bool
    {
        return \in_array(
            $severity,
            [
                E_COMPILE_ERROR,
                E_CORE_ERROR,
                E_USER_ERROR,
                E_PARSE,
                E_ERROR,
            ],
            true
        );
    }
}
