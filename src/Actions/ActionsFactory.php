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

    public function createAddExpectation(): AddExpectationAction
    {
        return new AddExpectationAction(
            $this->serverFactory->createRequestToExpectationMapper(),
            $this->serverFactory->createExpectationStorage(),
            $this->serverFactory->createLogger()
        );
    }

    public function createClearExpectations(): ClearExpectationsAction
    {
        return new ClearExpectationsAction($this->serverFactory->createExpectationStorage());
    }

    public function createClearScenarios(): ClearScenariosAction
    {
        return new ClearScenariosAction($this->serverFactory->createScenarioStorage());
    }

    public function createCountRequests(): CountRequestsAction
    {
        return new CountRequestsAction(
            $this->serverFactory->createRequestToExpectationMapper(),
            $this->serverFactory->createRequestStorage(),
            $this->serverFactory->createRequestExpectationComparator(),
            $this->serverFactory->createLogger()
        );
    }

    public function createListExpectations(): ListExpectationsAction
    {
        return new ListExpectationsAction(
            $this->serverFactory->createExpectationStorage(),
            $this->phiremockFactory->createExpectationToArrayConverter()
        );
    }

    public function createListRequests(): ListRequestsAction
    {
        return new ListRequestsAction(
            $this->serverFactory->createRequestToExpectationMapper(),
            $this->serverFactory->createRequestStorage(),
            $this->serverFactory->createRequestExpectationComparator(),
            $this->serverFactory->createLogger()
        );
    }

    public function createReloadPreconfiguredExpectations(): ReloadPreconfiguredExpectationsAction
    {
        return new ReloadPreconfiguredExpectationsAction(
            $this->serverFactory->createExpectationStorage(),
            $this->serverFactory->createExpectationBackup(),
            $this->serverFactory->createLogger()
        );
    }

    public function createResetRequestsCount(): ResetRequestsCountAction
    {
        return new ResetRequestsCountAction($this->serverFactory->createRequestStorage());
    }

    public function createReset(): ResetAction
    {
        return new ResetAction(
            $this->createClearScenarios(),
            $this->createResetRequestsCount(),
            $this->createReloadPreconfiguredExpectations(),
            $this->serverFactory->createLogger()
        );
    }

    public function createSearchRequest(): SearchRequestAction
    {
        return new SearchRequestAction(
            $this->serverFactory->createExpectationStorage(),
            $this->serverFactory->createRequestExpectationComparator(),
            $this->serverFactory->createResponseStrategyLocator(),
            $this->serverFactory->createRequestStorage(),
            $this->serverFactory->createLogger()
        );
    }

    public function createSetScenarioState(): SetScenarioStateAction
    {
        return new SetScenarioStateAction(
            $this->phiremockFactory->createArrayToScenarioStateInfoConverter(),
            $this->serverFactory->createScenarioStorage(),
            $this->serverFactory->createLogger()
        );
    }
}
