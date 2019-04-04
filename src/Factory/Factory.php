<?php

namespace Mcustiel\Phiremock\Server\Factory;

use Mcustiel\Creature\SingletonLazyCreator;
use Mcustiel\Phiremock\Factory as PhiremockFactory;
use Mcustiel\Phiremock\Server\Actions\AddExpectationAction;
use Mcustiel\Phiremock\Server\Actions\ClearExpectationsAction;
use Mcustiel\Phiremock\Server\Actions\ClearScenariosAction;
use Mcustiel\Phiremock\Server\Actions\CountRequestsAction;
use Mcustiel\Phiremock\Server\Actions\ListExpectationsAction;
use Mcustiel\Phiremock\Server\Actions\ListRequestsAction;
use Mcustiel\Phiremock\Server\Actions\ReloadPreconfiguredExpectationsAction;
use Mcustiel\Phiremock\Server\Actions\ResetRequestsCountAction;
use Mcustiel\Phiremock\Server\Actions\SearchRequestAction;
use Mcustiel\Phiremock\Server\Actions\SetScenarioStateAction;
use Mcustiel\Phiremock\Server\Actions\StoreRequestAction;
use Mcustiel\Phiremock\Server\Actions\VerifyRequestFound;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Mcustiel\Phiremock\Server\Config\RouterConfig;
use Mcustiel\Phiremock\Server\Http\Implementation\ReactPhpServer;
use Mcustiel\Phiremock\Server\Http\InputSources\UrlFromPath;
use Mcustiel\Phiremock\Server\Http\Matchers\JsonObjectsEquals;
use Mcustiel\Phiremock\Server\Model\Implementation\ExpectationAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\RequestAutoStorage;
use Mcustiel\Phiremock\Server\Model\Implementation\ScenarioAutoStorage;
use Mcustiel\Phiremock\Server\Phiremock;
use Mcustiel\Phiremock\Server\Utils\DataStructures\StringObjectArrayMap;
use Mcustiel\Phiremock\Server\Utils\FileExpectationsLoader;
use Mcustiel\Phiremock\Server\Utils\HomePathService;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Utils\ResponseStrategyLocator;
use Mcustiel\Phiremock\Server\Utils\Strategies\HttpResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\ProxyResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\RegexResponseStrategy;
use Mcustiel\PowerRoute\Actions\ServerError;
use Mcustiel\PowerRoute\Common\Conditions\ConditionsMatcherFactory;
use Mcustiel\PowerRoute\Common\Factories\ActionFactory;
use Mcustiel\PowerRoute\Common\Factories\InputSourceFactory;
use Mcustiel\PowerRoute\Common\Factories\MatcherFactory;
use Mcustiel\PowerRoute\InputSources\Body;
use Mcustiel\PowerRoute\InputSources\Header;
use Mcustiel\PowerRoute\InputSources\Method;
use Mcustiel\PowerRoute\Matchers\CaseInsensitiveEquals;
use Mcustiel\PowerRoute\Matchers\Contains as ContainsMatcher;
use Mcustiel\PowerRoute\Matchers\Equals;
use Mcustiel\PowerRoute\Matchers\RegExp as RegExpMatcher;
use Mcustiel\PowerRoute\PowerRoute;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Factory
{
    /** @var PhiremockFactory */
    private $phiremockFactory;

    /** @var StringObjectArrayMap */
    private $factoryCache;

    /** @var array */
    private $routerConfigCache;

    public function __construct(PhiremockFactory $factory)
    {
        $this->phiremockFactory = $factory;
        $this->factoryCache = new StringObjectArrayMap();
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

    public function createRouterConfig()
    {
        if ($this->routerConfigCache === null) {
            $this->routerConfigCache = RouterConfig::get();
        }

        return $this->routerConfigCache;
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
                    $this->createMatcherFactory(),
                    $this->createInputSourceFactory(),
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

    public function createConditionsMatcherFactory()
    {
        if (!$this->factoryCache->has('conditionsMatcherFactory')) {
            $this->factoryCache->set(
                'conditionsMatcherFactory',
                new ConditionsMatcherFactory(
                    $this->createInputSourceFactory(),
                    $this->createMatcherFactory()
                )
            );
        }

        return $this->factoryCache->get('conditionsMatcherFactory');
    }

    public function createMatcherFactory()
    {
        if (!$this->factoryCache->has('matcherFactory')) {
            $this->factoryCache->set(
                'matcherFactory',
                new MatcherFactory([
                    Matchers::EQUAL_TO    => new SingletonLazyCreator(Equals::class),
                    Matchers::MATCHES     => new SingletonLazyCreator(RegExpMatcher::class),
                    Matchers::SAME_STRING => new SingletonLazyCreator(CaseInsensitiveEquals::class),
                    Matchers::CONTAINS    => new SingletonLazyCreator(ContainsMatcher::class),
                    Matchers::SAME_JSON   => new SingletonLazyCreator(
                        JsonObjectsEquals::class,
                        [$this->createLogger()]
                    ),
                ])
            );
        }

        return $this->factoryCache->get('matcherFactory');
    }

    public function createInputSourceFactory()
    {
        if (!$this->factoryCache->has('inputSourceFactory')) {
            $this->factoryCache->set(
                'inputSourceFactory',
                new InputSourceFactory([
                    'method' => new SingletonLazyCreator(Method::class),
                    'url'    => new SingletonLazyCreator(UrlFromPath::class),
                    'header' => new SingletonLazyCreator(Header::class),
                    'body'   => new SingletonLazyCreator(Body::class),
                ])
            );
        }

        return $this->factoryCache->get('inputSourceFactory');
    }

    public function createRouter()
    {
        if (!$this->factoryCache->has('router')) {
            $this->factoryCache->set(
                'router',
                new PowerRoute(
                    $this->createRouterConfig(),
                    $this->createActionFactory(),
                    $this->createConditionsMatcherFactory()
                )
            );
        }

        return $this->factoryCache->get('router');
    }

    public function createActionFactory()
    {
        if (!$this->factoryCache->has('actionFactory')) {
            $this->factoryCache->set(
                'actionFactory',
                new ActionFactory([
                    'addExpectation' => new SingletonLazyCreator(
                        AddExpectationAction::class,
                        [
                            $this->phiremockFactory->createArrayToExpectationConverter(),
                            $this->createExpectationStorage(),
                            $this->createLogger(),
                        ]
                    ),
                    'listExpectations' => new SingletonLazyCreator(
                        ListExpectationsAction::class,
                        [$this->createExpectationStorage(), $this->phiremockFactory->createExpectationToArrayConverter()]
                    ),
                    'reloadExpectations' => new SingletonLazyCreator(
                        ReloadPreconfiguredExpectationsAction::class,
                        [
                            $this->createExpectationStorage(),
                            $this->createExpectationBackup(),
                            $this->createLogger(),
                        ]
                    ),
                    'clearExpectations' => new SingletonLazyCreator(
                        ClearExpectationsAction::class,
                        [$this->createExpectationStorage()]
                    ),
                    'serverError' => new SingletonLazyCreator(
                        ServerError::class
                    ),
                    'setScenarioState' => new SingletonLazyCreator(
                        SetScenarioStateAction::class,
                        [
                            $this->phiremockFactory->createArrayToExpectationConverter(),
                            $this->createScenarioStorage(),
                            $this->createLogger(),
                        ]
                    ),
                    'clearScenarios' => new SingletonLazyCreator(
                        ClearScenariosAction::class,
                        [$this->createScenarioStorage()]
                    ),
                    'checkExpectations' => new SingletonLazyCreator(
                        SearchRequestAction::class,
                        [
                            $this->createExpectationStorage(),
                            $this->createRequestExpectationComparator(),
                            $this->createLogger(),
                        ]
                    ),
                    'verifyExpectations' => new SingletonLazyCreator(
                        VerifyRequestFound::class,
                        [
                            $this->createScenarioStorage(),
                            $this->createLogger(),
                            $this->createResponseStrategyLocator(),
                        ]
                    ),
                    'countRequests' => new SingletonLazyCreator(
                        CountRequestsAction::class,
                        [
                            $this->phiremockFactory->createArrayToExpectationConverter(),
                            $this->createRequestStorage(),
                            $this->createRequestExpectationComparator(),
                            $this->createLogger(),
                        ]
                    ),
                    'listRequests' => new SingletonLazyCreator(
                        ListRequestsAction::class,
                        [
                            $this->phiremockFactory->createArrayToExpectationConverter(),
                            $this->createRequestStorage(),
                            $this->createRequestExpectationComparator(),
                            $this->createLogger(),
                        ]
                    ),
                    'resetCount' => new SingletonLazyCreator(
                        ResetRequestsCountAction::class,
                        [$this->createRequestStorage()]
                    ),
                    'storeRequest' => new SingletonLazyCreator(
                        StoreRequestAction::class,
                        [$this->createRequestStorage()]
                    ),
                ])
            );
        }

        return $this->factoryCache->get('actionFactory');
    }
}
