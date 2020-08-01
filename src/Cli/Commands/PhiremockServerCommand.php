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

use Mcustiel\Phiremock\Server\Factory\Factory;
use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Mcustiel\Phiremock\Server\Utils\Config\Config;
use Mcustiel\Phiremock\Server\Utils\Config\ConfigBuilder;
use Mcustiel\Phiremock\Server\Utils\Config\Directory;
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
    const CONFIG_PATH_HELP_MESSAGE = 'Directory in which to search for configuration files. Default: current directory.';

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
            ->setHelp('This is the main command to run Phiremock as a HTTP server.')
            ->addOption(
                'ip',
                'i',
                InputOption::VALUE_REQUIRED,
                self::IP_HELP_MESSAGE
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_REQUIRED,
                self::PORT_HELP_MESSAGE
            )
            ->addOption(
                'expectations-dir',
                'e',
                InputOption::VALUE_REQUIRED,
                self::EXPECTATIONS_DIR_HELP_MESSAGE
            )
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_NONE,
                sprintf(self::DEBUG_HELP_MESSAGE)
            )
            ->addOption(
                'config-path',
                'c',
                InputOption::VALUE_REQUIRED,
                sprintf(self::CONFIG_PATH_HELP_MESSAGE)
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createPhiremockPathIfNotExists();

        $configPath = new Directory($input->getOption('config-path') ?? getcwd());
        $cliConfig = [];
        if ($input->getOption('ip')) {
            $cliConfig['ip'] = (string) $input->getOption('ip');
        }
        if ($input->getOption('port')) {
            $cliConfig['port'] = (int) $input->getOption('port');
        }
        if ($input->getOption('debug')) {
            $cliConfig['debug'] = true;
        }
        if ($input->getOption('expectations-dir')) {
            $cliConfig['expectations-dir'] = $this->factory
                ->createFileSystemService()
                ->getRealPath((string) $input->getOption('expectations-dir'));
        }

        $config = (new ConfigBuilder($configPath))->build($cliConfig);

        $this->initializeLogger($config);
        $this->processFileExpectations($config);
        $this->startHttpServer($config);

        return 0;
    }

    private function createPhiremockPathIfNotExists()
    {
        $defaultExpectationsPath = ConfigBuilder::getDefaultExpectationsDir();
        if (!$defaultExpectationsPath->exists()) {
            $defaultExpectationsPath->create();
        } elseif (!$defaultExpectationsPath->isDirectory()) {
            throw new \Exception('Expectations path must be a directory');
        }
    }

    private function startHttpServer(Config $config): void
    {
        $httpServer = $this->factory->createHttpServer();
        $this->setUpHandlers($httpServer);
        $httpServer->listen($config->getInterfaceIp(), $config->getPort());
    }

    private function initializeLogger(Config $config): void
    {
        \define('IS_DEBUG_MODE', $config->isDebugMode());

        $this->logger = $this->factory->createLogger();
        $this->logger->info(
            sprintf(
                '[%s] Starting Phiremock%s...',
                date('Y-m-d H:i:s'),
                (IS_DEBUG_MODE ? ' in debug mode' : '')
            )
        );
    }

    private function processFileExpectations(Config $config): void
    {
        $expectationsDir = $config->getExpectationsPath()->asString();
        $this->logger->debug("Phiremock's expectation dir is set to: {$expectationsDir}");
        $this->factory
            ->createFileExpectationsLoader()
            ->loadExpectationsFromDirectory($expectationsDir);
    }

    private function setUpHandlers(ServerInterface $server): void
    {
        $handleTermination = function () use ($server) {
            $this->logger->info('Stopping Phiremock...');
            $server->shutdown();
            $this->logger->info('Bye bye');
        };

        $this->logger->debug('Registering shutdown function');
        register_shutdown_function($handleTermination);

        if (\function_exists('pcntl_signal')) {
            $this->logger->debug('PCNTL present: Installing signal handlers');
            pcntl_signal(SIGTERM, function() { exit(0); });
        }

        $errorHandler = function ($severity, $message, $file, $line) {
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
