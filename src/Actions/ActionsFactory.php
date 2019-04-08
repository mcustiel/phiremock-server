<?php

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Factory as PhiremockFactory;
use Mcustiel\Phiremock\Server\Factory\Factory as PhiremockServerFactory;
use Mcustiel\Phiremock\Server\Utils\DataStructures\StringObjectArrayMap;

class ActionsFactory
{
    /** @var StringObjectArrayMap */
    private $factoryCache;
    /** @var PhiremockServerFactory */
    private $serverFactory;
    /** @var PhiremockFactory */
    private $phiremockFactory;

    public function __construct(
        PhiremockServerFactory $serverFactory,
        PhiremockFactory $phiremockFactory
    ) {
        $this->factoryCache = new StringObjectArrayMap();
        $this->serverFactory = $serverFactory;
        $this->phiremockFactory = $phiremockFactory;
    }

    public function createAddExpectation()
    {
        return new AddExpectationAction(
            $this->phiremockFactory->createArrayToExpectationConverter(),
            $this->serverFactory->createExpectationStorage(),
            $this->serverFactory->createLogger()
        );
    }

    public function createClearExpectations()
    {
        return new ClearExpectationsAction($this->serverFactory->createExpectationStorage());
    }

    public function createClearScenarios()
    {
        return new ClearScenariosAction($this->serverFactory->createScenarioStorage());
    }

    public function createCountRequests()
    {
        return new CountRequestsAction(
            $this->phiremockFactory->createArrayToExpectationConverter(),
            $this->serverFactory->createRequestStorage(),
            $this->serverFactory->createRequestExpectationComparator(),
            $this->serverFactory->createLogger()
        );
    }

    public function createListExpectations()
    {
        return new ListExpectationsAction(
            $this->serverFactory->createExpectationStorage(),
            $this->phiremockFactory->createExpectationToArrayConverter()
        );
    }

    public function createListRequests()
    {
        return new ListRequestsAction(
            $this->phiremockFactory->createArrayToRequestConditionConverter(),
            $this->serverFactory->createRequestStorage(),
            $this->serverFactory->createRequestExpectationComparator(),
            $this->serverFactory->createLogger()
        );
    }

    public function createReloadPreconfiguredExpectations()
    {
        return new ReloadPreconfiguredExpectationsAction(
            $this->serverFactory->createExpectationStorage(),
            $this->serverFactory->createExpectationBackup(),
            $this->serverFactory->createLogger()
        );
    }

    public function createResetRequestsCount()
    {
        return new ResetRequestsCountAction($this->serverFactory->createRequestStorage());
    }

    public function createSearchRequest()
    {
        return new SearchRequestAction(
            $this->serverFactory->createExpectationStorage(),
            $this->serverFactory->createRequestExpectationComparator(),
            $this->serverFactory->createScenarioStorage(),
            $this->serverFactory->createResponseStrategyLocator(),
            $this->serverFactory->createRequestStorage(),
            $this->serverFactory->createLogger()
        );
    }

    public function createSetScenarioState()
    {
        return new SetScenarioStateAction(
            $this->phiremockFactory->createArrayToScenarioStateInfoConverter(),
            $this->serverFactory->createScenarioStorage(),
            $this->serverFactory->createLogger()
        );
    }
}
