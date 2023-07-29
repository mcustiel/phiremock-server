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

namespace Mcustiel\Phiremock\Server\Factory;

use Exception;
use Mcustiel\Phiremock\Common\Utils\FileSystem;
use Mcustiel\Phiremock\Factory as PhiremockFactory;
use Mcustiel\Phiremock\Server\Actions\ActionLocator;
use Mcustiel\Phiremock\Server\Actions\ActionsFactory;
use Mcustiel\Phiremock\Server\Http\Implementation\FastRouterHandler;
use Mcustiel\Phiremock\Server\Http\Implementation\ReactPhpServer;
use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ExpectationAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\RequestAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ScenarioAutoStorage;
use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\Phiremock\Server\Utils\Config\Config;
use Mcustiel\Phiremock\Server\Utils\DataStructures\StringObjectArrayMap;
use Mcustiel\Phiremock\Server\Utils\FileExpectationsLoader;
use Mcustiel\Phiremock\Server\Utils\GuzzlePsr18Client;
use Mcustiel\Phiremock\Server\Utils\HomePathService;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Utils\RequestToExpectationMapper;
use Mcustiel\Phiremock\Server\Utils\ResponseStrategyLocator;
use Mcustiel\Phiremock\Server\Utils\Strategies\HttpResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\ProxyResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\RegexProxyResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\RegexResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\Utils\RegexReplacer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class Factory
{
    /** @var PhiremockFactory */
    private $phiremockFactory;

    /** @var StringObjectArrayMap */
    private $factoryCache;

    /** @var Config */
    private $config;

    public function __construct(PhiremockFactory $factory, Config $config)
    {
        $this->phiremockFactory = $factory;
        $this->factoryCache = new StringObjectArrayMap();
        $this->config = $config;
    }

    public static function createDefault(Config $config): self
    {
        return new static(new PhiremockFactory(), $config);
    }

    public function createFileSystemService(): FileSystem
    {
        if (!$this->factoryCache->has('fileSystem')) {
            $this->factoryCache->set('fileSystem', new FileSystem());
        }

        return $this->factoryCache->get('fileSystem');
    }

    public function createLogger(): LoggerInterface
    {
        if (!$this->factoryCache->has('logger')) {
            $logger = new Logger('stdoutLogger');
            $logLevel = $this->config->isDebugMode() ? Logger::DEBUG : Logger::INFO;
            $logger->pushHandler(new StreamHandler(\STDOUT, $logLevel));
            $this->factoryCache->set('logger', $logger);
        }

        return $this->factoryCache->get('logger');
    }

    public function createHttpResponseStrategy(): HttpResponseStrategy
    {
        if (!$this->factoryCache->has('httpResponseStrategy')) {
            $this->factoryCache->set(
                'httpResponseStrategy',
                new HttpResponseStrategy(
                    $this->createScenarioStorage(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('httpResponseStrategy');
    }

    public function createRegexResponseStrategy(): RegexResponseStrategy
    {
        if (!$this->factoryCache->has('regexResponseStrategy')) {
            $this->factoryCache->set(
                'regexResponseStrategy',
                new RegexResponseStrategy(
                    $this->createScenarioStorage(),
                    $this->createLogger(),
                    $this->createRegexReplacer()
                )
            );
        }

        return $this->factoryCache->get('regexResponseStrategy');
    }

    /** @throws Exception */
    public function createProxyResponseStrategy(): ProxyResponseStrategy
    {
        if (!$this->factoryCache->has('proxyResponseStrategy')) {
            $this->factoryCache->set(
                'proxyResponseStrategy',
                new ProxyResponseStrategy(
                    $this->createScenarioStorage(),
                    $this->createLogger(),
                    $this->createHttpClient()
                )
            );
        }

        return $this->factoryCache->get('proxyResponseStrategy');
    }

    /** @throws Exception */
    public function createRegexProxyResponseStrategy(): RegexProxyResponseStrategy
    {
        if (!$this->factoryCache->has('regexProxyResponseStrategy')) {
            $this->factoryCache->set(
                'regexProxyResponseStrategy',
                new RegexProxyResponseStrategy(
                    $this->createScenarioStorage(),
                    $this->createLogger(),
                    $this->createHttpClient(),
                    $this->createRegexReplacer()
                )
            );
        }

        return $this->factoryCache->get('regexProxyResponseStrategy');
    }

    public function createRegexReplacer(): RegexReplacer
    {
        if (!$this->factoryCache->has('regexReplacer')) {
            $this->factoryCache->set(
                'regexReplacer',
                new RegexReplacer()
            );
        }

        return $this->factoryCache->get('regexReplacer');
    }

    public function createResponseStrategyLocator(): ResponseStrategyLocator
    {
        if (!$this->factoryCache->has('responseStrategyLocator')) {
            $this->factoryCache->set(
                'responseStrategyLocator',
                new ResponseStrategyLocator($this)
            );
        }

        return $this->factoryCache->get('responseStrategyLocator');
    }

    public function createRequestsRouter(): FastRouterHandler
    {
        if (!$this->factoryCache->has('router')) {
            $this->factoryCache->set(
                'router',
                new FastRouterHandler($this->createActionLocator(), $this->config, $this->createLogger())
            );
        }

        return $this->factoryCache->get('router');
    }

    public function createHomePathService(): HomePathService
    {
        if (!$this->factoryCache->has('homePathService')) {
            $this->factoryCache->set(
                'homePathService',
                new HomePathService()
            );
        }

        return $this->factoryCache->get('homePathService');
    }

    public function createHttpServer(): ServerInterface
    {
        if (!$this->factoryCache->has('httpServer')) {
            $this->factoryCache->set(
                'httpServer',
                new ReactPhpServer($this->createRequestsRouter(), $this->createLogger())
            );
        }

        return $this->factoryCache->get('httpServer');
    }

    public function createExpectationStorage(): ExpectationStorage
    {
        if (!$this->factoryCache->has('expectationsStorage')) {
            $this->factoryCache->set(
                'expectationsStorage',
                new ExpectationAutoStorage()
            );
        }

        return $this->factoryCache->get('expectationsStorage');
    }

    public function createExpectationBackup(): ExpectationStorage
    {
        if (!$this->factoryCache->has('expectationsBackup')) {
            $this->factoryCache->set(
                'expectationsBackup',
                new ExpectationAutoStorage()
            );
        }

        return $this->factoryCache->get('expectationsBackup');
    }

    public function createRequestStorage(): RequestStorage
    {
        if (!$this->factoryCache->has('requestsStorage')) {
            $this->factoryCache->set(
                'requestsStorage',
                new RequestAutoStorage()
            );
        }

        return $this->factoryCache->get('requestsStorage');
    }

    public function createScenarioStorage(): ScenarioStorage
    {
        if (!$this->factoryCache->has('scenariosStorage')) {
            $this->factoryCache->set(
                'scenariosStorage',
                new ScenarioAutoStorage()
            );
        }

        return $this->factoryCache->get('scenariosStorage');
    }

    public function createRequestExpectationComparator(): RequestExpectationComparator
    {
        if (!$this->factoryCache->has('requestExpectationComparator')) {
            $this->factoryCache->set(
                'requestExpectationComparator',
                new RequestExpectationComparator(
                    $this->createScenarioStorage(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('requestExpectationComparator');
    }

    public function createFileExpectationsLoader(): FileExpectationsLoader
    {
        if (!$this->factoryCache->has('fileExpectationsLoader')) {
            $this->factoryCache->set(
                'fileExpectationsLoader',
                new FileExpectationsLoader(
                    $this->phiremockFactory->createArrayToExpectationConverterLocator(),
                    $this->createExpectationStorage(),
                    $this->createExpectationBackup(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('fileExpectationsLoader');
    }

    public function createActionLocator(): ActionLocator
    {
        if (!$this->factoryCache->has('actionLocator')) {
            $this->factoryCache->set(
                'actionLocator',
                new ActionLocator($this->createActionFactory())
            );
        }

        return $this->factoryCache->get('actionLocator');
    }

    public function createActionFactory(): ActionsFactory
    {
        if (!$this->factoryCache->has('actionFactory')) {
            $this->factoryCache->set(
                'actionFactory',
                new ActionsFactory($this, $this->phiremockFactory)
            );
        }

        return $this->factoryCache->get('actionFactory');
    }

    public function createRequestToExpectationMapper(): RequestToExpectationMapper
    {
        if (!$this->factoryCache->has('requestToExpectationMapper')) {
            $this->factoryCache->set(
                'requestToExpectationMapper',
                new RequestToExpectationMapper(
                    $this->phiremockFactory->createArrayToExpectationConverterLocator(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('requestToExpectationMapper');
    }

    /** @throws Exception */
    public function createHttpClient(): ClientInterface
    {
        if (!class_exists('\GuzzleHttp\Client', true)) {
            throw new Exception('A default http client implementation is needed. ' . 'Please extend the factory to return a PSR18-compatible HttpClient or install Guzzle Http Client v6');
        }

        return new GuzzlePsr18Client();
    }
}
