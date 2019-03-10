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

    public function __construct(PhiremockFactory $factory)
    {
        $this->phiremockFactory = $factory;
    }

    /** @return \Monolog\Logger */
    public function createLogger()
    {
        $log = new Logger('stdoutLogger');
        $log->pushHandler(new StreamHandler(STDOUT, LOG_LEVEL));

        return $log;
    }

    /** @return \Mcustiel\Phiremock\Server\Utils\Strategies\HttpResponseStrategy */
    public function createHttpResponseStrategy()
    {
        return new HttpResponseStrategy($this->createLogger());
    }

    /** @return \Mcustiel\Phiremock\Server\Utils\Strategies\RegexResponseStrategy */
    public function createRegexResponseStrategy()
    {
        return new RegexResponseStrategy($this->createLogger());
    }

    public function createProxyResponseStrategy()
    {
        return new ProxyResponseStrategy(
            $this->phiremockFactory->createRemoteConnectionInterface(),
            $this->createLogger()
        );
    }

    public function createResponseStrategyLocator()
    {
        return new ResponseStrategyLocator($this);
    }

    public function createRouterConfig()
    {
        return RouterConfig::get();
    }

    public function createHomePathService()
    {
        return new HomePathService();
    }

    public function createHttpServer()
    {
        return new ReactPhpServer($this->createLogger());
    }

    public function createPhiremockApplication()
    {
        return new Phiremock($this->createRouter(), $this->createLogger());
    }

    public function createExpectationStorage()
    {
        return new ExpectationAutoStorage();
    }

    public function createExpectationBackup()
    {
        return new ExpectationAutoStorage();
    }

    public function createRequestStorage()
    {
        return new RequestAutoStorage();
    }

    public function createScenarioStorage()
    {
        return new ScenarioAutoStorage();
    }

    public function createRequestExpectationComparator()
    {
        return new RequestExpectationComparator(
            $this->createMatcherFactory(),
            $this->createInputSourceFactory(),
            $this->createScenarioStorage(),
            $this->createLogger()
        );
    }

    public function createFileExpectationsLoader()
    {
        return new FileExpectationsLoader(
            $this->phiremockFactory->createArrayToExpectationConverter(),
            $this->createExpectationStorage(),
            $this->createExpectationBackup(),
            $this->createLogger()
        );
    }

    public function createConditionsMatcherFactory()
    {
        return new ConditionsMatcherFactory(
            $this->createInputSourceFactory(),
            $this->createMatcherFactory()
        );
    }

    public function createMatcherFactory()
    {
        return new MatcherFactory([
            Matchers::EQUAL_TO    => new SingletonLazyCreator(Equals::class),
            Matchers::MATCHES     => new SingletonLazyCreator(RegExpMatcher::class),
            Matchers::SAME_STRING => new SingletonLazyCreator(CaseInsensitiveEquals::class),
            Matchers::CONTAINS    => new SingletonLazyCreator(ContainsMatcher::class),
            Matchers::SAME_JSON   => new SingletonLazyCreator(
                JsonObjectsEquals::class,
                [$this->createLogger()]
            ),
        ]);
    }

    public function createInputSourceFactory()
    {
        return new InputSourceFactory([
            'method' => new SingletonLazyCreator(Method::class),
            'url'    => new SingletonLazyCreator(UrlFromPath::class),
            'header' => new SingletonLazyCreator(Header::class),
            'body'   => new SingletonLazyCreator(Body::class),
        ]);
    }

    public function createRouter()
    {
        return new PowerRoute(
            $this->createRouterConfig(),
            $this->createActionFactory(),
            $this->createConditionsMatcherFactory()
        );
    }

    public function createActionFactory()
    {
        return new ActionFactory([
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
                [$this->createExpectationStorage()]
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
        ]);
    }
}
