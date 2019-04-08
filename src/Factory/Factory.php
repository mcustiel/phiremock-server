<?php

namespace Mcustiel\Phiremock\Server\Factory;

use Mcustiel\Phiremock\Factory as PhiremockFactory;
use Mcustiel\Phiremock\Server\Actions\ActionLocator;
use Mcustiel\Phiremock\Server\Actions\ActionsFactory;
use Mcustiel\Phiremock\Server\Http\Implementation\ReactPhpServer;
use Mcustiel\Phiremock\Server\Http\InputSources\InputSourceFactory as PhiremockInputSourceFactory;
use Mcustiel\Phiremock\Server\Http\InputSources\InputSourceLocator;
use Mcustiel\Phiremock\Server\Http\Matchers\MatcherFactory as PhiremockMatcherFactory;
use Mcustiel\Phiremock\Server\Http\Matchers\MatcherLocator;
use Mcustiel\Phiremock\Server\Model\Implementation\ExpectationAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\RequestAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ScenarioAutoStorage;
use Mcustiel\Phiremock\Server\Phiremock;
use Mcustiel\Phiremock\Server\Utils\DataStructures\StringObjectArrayMap;
use Mcustiel\Phiremock\Server\Utils\FileExpectationsLoader;
use Mcustiel\Phiremock\Server\Utils\HomePathService;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Utils\ResponseStrategyLocator;
use Mcustiel\Phiremock\Server\Utils\Router\FastRouterRouter;
use Mcustiel\Phiremock\Server\Utils\Strategies\HttpResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\ProxyResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\RegexResponseStrategy;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
        $this->matchersFactory = new \Mcustiel\Phiremock\Server\Http\Matchers\MatcherFactory($this);
    }

    /** @return \Monolog\Logger */
    public function createLogger()
    {
        if (!$this->factoryCache->has('logger')) {
            $log = new Logger('stdoutLogger');
            $log->pushHandler(new StreamHandler(STDOUT, LOG_LEVEL));
            $this->factoryCache->set('logger', $log);
        }

        return $this->factoryCache->get('logger');
    }

    /** @return \Mcustiel\Phiremock\Server\Utils\Strategies\HttpResponseStrategy */
    public function createHttpResponseStrategy()
    {
        if (!$this->factoryCache->has('httpResponseStrategy')) {
            $this->factoryCache->set(
                'httpResponseStrategy',
                new HttpResponseStrategy($this->createLogger())
            );
        }

        return $this->factoryCache->get('httpResponseStrategy');
    }

    /** @return \Mcustiel\Phiremock\Server\Utils\Strategies\RegexResponseStrategy */
    public function createRegexResponseStrategy()
    {
        if (!$this->factoryCache->has('regexResponseStrategy')) {
            $this->factoryCache->set(
                'regexResponseStrategy',
                new RegexResponseStrategy($this->createLogger())
            );
        }

        return $this->factoryCache->get('regexResponseStrategy');
    }

    public function createProxyResponseStrategy()
    {
        if (!$this->factoryCache->has('proxyResponseStrategy')) {
            $this->factoryCache->set(
                'proxyResponseStrategy',
                new ProxyResponseStrategy(
                    $this->phiremockFactory->createRemoteConnectionInterface(),
                    $this->createLogger()
                )
            );
        }

        return $this->factoryCache->get('proxyResponseStrategy');
    }

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

    public function createRouter()
    {
        if (!$this->factoryCache->has('router')) {
            $this->factoryCache->set(
                'router',
                new FastRouterRouter($this->createActionLocator())
            );
        }

        return $this->factoryCache->get('router');
    }

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

    public function createHttpServer()
    {
        if (!$this->factoryCache->has('httpServer')) {
            $this->factoryCache->set(
                'httpServer',
                new ReactPhpServer($this->createLogger())
            );
        }

        return $this->factoryCache->get('httpServer');
    }

    public function createPhiremockApplication()
    {
        if (!$this->factoryCache->has('phiremockApp')) {
            $this->factoryCache->set(
                'phiremockApp',
                new Phiremock($this->createRouter(), $this->createLogger())
            );
        }

        return $this->factoryCache->get('phiremockApp');
    }

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
}
