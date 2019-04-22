<?php

namespace Mcustiel\Phiremock\Server\Factory;

use Mcustiel\Phiremock\Common\Utils\FileSystem;
use Mcustiel\Phiremock\Factory as PhiremockFactory;
use Mcustiel\Phiremock\Server\Actions\ActionLocator;
use Mcustiel\Phiremock\Server\Actions\ActionsFactory;
use Mcustiel\Phiremock\Server\Http\Implementation\FastRouterHandler;
use Mcustiel\Phiremock\Server\Http\Implementation\ReactPhpServer;
use Mcustiel\Phiremock\Server\Http\InputSources\InputSourceFactory;
use Mcustiel\Phiremock\Server\Http\InputSources\InputSourceFactory as PhiremockInputSourceFactory;
use Mcustiel\Phiremock\Server\Http\InputSources\InputSourceLocator;
use Mcustiel\Phiremock\Server\Http\Matchers\MatcherFactory;
use Mcustiel\Phiremock\Server\Http\Matchers\MatcherFactory as PhiremockMatcherFactory;
use Mcustiel\Phiremock\Server\Http\Matchers\MatcherLocator;
use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ExpectationAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\RequestAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ScenarioAutoStorage;
use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\Phiremock\Server\Utils\DataStructures\StringObjectArrayMap;
use Mcustiel\Phiremock\Server\Utils\FileExpectationsLoader;
use Mcustiel\Phiremock\Server\Utils\HomePathService;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Utils\RequestToMockConfigMapper;
use Mcustiel\Phiremock\Server\Utils\ResponseStrategyLocator;
use Mcustiel\Phiremock\Server\Utils\Strategies\HttpResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\ProxyResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\RegexResponseStrategy;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Factory
{
    /** @var PhiremockFactory */
    private $phiremockFactory;

    /** @var StringObjectArrayMap */
    private $factoryCache;

    public function __construct(PhiremockFactory $factory)
    {
        $this->phiremockFactory = $factory;
        $this->factoryCache = new StringObjectArrayMap();
    }

    /** @return FileSystem */
    public function createFileSystemService()
    {
        if (!$this->factoryCache->has('fileSystem')) {
            $this->factoryCache->set('fileSystem', new FileSystem());
        }

        return $this->factoryCache->get('fileSystem');
    }

    /** @return LoggerInterface */
    public function createLogger()
    {
        if (!$this->factoryCache->has('logger')) {
            $logger = new Logger('stdoutLogger');
            $logLevel = IS_DEBUG_MODE ? \Monolog\Logger::DEBUG : \Monolog\Logger::INFO;
            $logger->pushHandler(new StreamHandler(STDOUT, $logLevel));
            $this->factoryCache->set('logger', $logger);
        }

        return $this->factoryCache->get('logger');
    }

    /** @return HttpResponseStrategy */
    public function createHttpResponseStrategy()
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

    /** @return RegexResponseStrategy */
    public function createRegexResponseStrategy()
    {
        if (!$this->factoryCache->has('regexResponseStrategy')) {
            $this->factoryCache->set(
                'regexResponseStrategy',
                new RegexResponseStrategy(
                    $this->createScenarioStorage(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('regexResponseStrategy');
    }

    /** @return ProxyResponseStrategy */
    public function createProxyResponseStrategy()
    {
        if (!$this->factoryCache->has('proxyResponseStrategy')) {
            $this->factoryCache->set(
                'proxyResponseStrategy',
                new ProxyResponseStrategy(
                    $this->phiremockFactory->createRemoteConnectionInterface(),
                    $this->createScenarioStorage(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('proxyResponseStrategy');
    }

    /** @return ResponseStrategyLocator */
    public function createResponseStrategyLocator()
    {
        if (!$this->factoryCache->has('responseStrategyLocator')) {
            $this->factoryCache->set(
                'responseStrategyLocator',
                new ResponseStrategyLocator($this)
            );
        }

        return $this->factoryCache->get('responseStrategyLocator');
    }

    /** @return FastRouterHandler */
    public function createRequestsRouter()
    {
        if (!$this->factoryCache->has('router')) {
            $this->factoryCache->set(
                'router',
                new FastRouterHandler($this->createActionLocator())
            );
        }

        return $this->factoryCache->get('router');
    }

    /** @return HomePathService */
    public function createHomePathService()
    {
        if (!$this->factoryCache->has('homePathService')) {
            $this->factoryCache->set(
                'homePathService',
                new HomePathService()
            );
        }

        return $this->factoryCache->get('homePathService');
    }

    /** @return ServerInterface */
    public function createHttpServer()
    {
        if (!$this->factoryCache->has('httpServer')) {
            $this->factoryCache->set(
                'httpServer',
                new ReactPhpServer($this->createRequestsRouter(), $this->createLogger())
            );
        }

        return $this->factoryCache->get('httpServer');
    }

    /** @return ExpectationStorage */
    public function createExpectationStorage()
    {
        if (!$this->factoryCache->has('expectationsStorage')) {
            $this->factoryCache->set(
                'expectationsStorage',
                new ExpectationAutoStorage()
            );
        }

        return $this->factoryCache->get('expectationsStorage');
    }

    /** @return ExpectationStorage */
    public function createExpectationBackup()
    {
        if (!$this->factoryCache->has('expectationsBackup')) {
            $this->factoryCache->set(
                'expectationsBackup',
                new ExpectationAutoStorage()
            );
        }

        return $this->factoryCache->get('expectationsBackup');
    }

    /** @return RequestStorage */
    public function createRequestStorage()
    {
        if (!$this->factoryCache->has('requestsStorage')) {
            $this->factoryCache->set(
                'requestsStorage',
                new RequestAutoStorage()
            );
        }

        return $this->factoryCache->get('requestsStorage');
    }

    /** @return ScenarioStorage */
    public function createScenarioStorage()
    {
        if (!$this->factoryCache->has('scenariosStorage')) {
            $this->factoryCache->set(
                'scenariosStorage',
                new ScenarioAutoStorage()
            );
        }

        return $this->factoryCache->get('scenariosStorage');
    }

    /** @return RequestExpectationComparator */
    public function createRequestExpectationComparator()
    {
        if (!$this->factoryCache->has('requestExpectationComparator')) {
            $this->factoryCache->set(
                'requestExpectationComparator',
                new RequestExpectationComparator(
                    $this->createMatcherLocator(),
                    $this->createInputSourceLocator(),
                    $this->createScenarioStorage(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('requestExpectationComparator');
    }

    /** @return FileExpectationsLoader */
    public function createFileExpectationsLoader()
    {
        if (!$this->factoryCache->has('fileExpectationsLoader')) {
            $this->factoryCache->set(
                'fileExpectationsLoader',
                new FileExpectationsLoader(
                    $this->phiremockFactory->createArrayToExpectationConverter(),
                    $this->createExpectationStorage(),
                    $this->createExpectationBackup(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('fileExpectationsLoader');
    }

    /** @return MatcherFactory */
    public function createMatcherFactory()
    {
        if (!$this->factoryCache->has('matcherFactory')) {
            $this->factoryCache->set(
                'matcherFactory',
                new PhiremockMatcherFactory($this)
            );
        }

        return $this->factoryCache->get('matcherFactory');
    }

    /** @return MatcherLocator */
    public function createMatcherLocator()
    {
        if (!$this->factoryCache->has('matcherLocator')) {
            $this->factoryCache->set(
                'matcherLocator',
                new MatcherLocator($this->createMatcherFactory())
            );
        }

        return $this->factoryCache->get('matcherLocator');
    }

    /** @return InputSourceFactory */
    public function createInputSourceFactory()
    {
        if (!$this->factoryCache->has('inputSourceFactory')) {
            $this->factoryCache->set(
                'inputSourceFactory',
                new PhiremockInputSourceFactory()
            );
        }

        return $this->factoryCache->get('inputSourceFactory');
    }

    /** @return InputSourceLocator */
    public function createInputSourceLocator()
    {
        if (!$this->factoryCache->has('inputSourceLocator')) {
            $this->factoryCache->set(
                'inputSourceLocator',
                new InputSourceLocator($this->createInputSourceFactory())
            );
        }

        return $this->factoryCache->get('inputSourceLocator');
    }

    /** @return ActionLocator */
    public function createActionLocator()
    {
        if (!$this->factoryCache->has('actionLocator')) {
            $this->factoryCache->set(
                'actionLocator',
                new ActionLocator($this->createActionFactory())
                );
        }

        return $this->factoryCache->get('actionLocator');
    }

    /** @return ActionsFactory */
    public function createActionFactory()
    {
        if (!$this->factoryCache->has('actionFactory')) {
            $this->factoryCache->set(
                'actionFactory',
                new ActionsFactory($this, $this->phiremockFactory)
            );
        }

        return $this->factoryCache->get('actionFactory');
    }

    /** @return RequestToMockConfigMapper */
    public function createRequestToMockConfigMapper()
    {
        if (!$this->factoryCache->has('requestToMockConfigMapper')) {
            $this->factoryCache->set(
                'requestToMockConfigMapper',
                new RequestToMockConfigMapper(
                    $this->phiremockFactory->createArrayToExpectationConverter(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('requestToMockConfigMapper');
    }
}
