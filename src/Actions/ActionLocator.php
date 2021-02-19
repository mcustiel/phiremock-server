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

use InvalidArgumentException;

class ActionLocator
{
    const LIST_EXPECTATIONS = 'listExpectations';
    const ADD_EXPECTATION = 'addExpectation';
    const CLEAR_EXPECTATIONS = 'clearExpectations';
    const SET_SCENARIO_STATE = 'setScenarioState';
    const CLEAR_SCENARIOS = 'clearScenarios';
    const COUNT_REQUESTS = 'countRequests';
    const LIST_REQUESTS = 'listRequests';
    const RESET_REQUESTS_COUNT = 'resetRequestsCount';
    const RESET = 'reset';

    const MANAGE_REQUEST = 'manageRequest';

    const ACTION_FACTORY_METHOD_MAP = [
        self::LIST_EXPECTATIONS  => 'createListExpectations',
        self::ADD_EXPECTATION    => 'createAddExpectation',
        self::CLEAR_EXPECTATIONS => 'createClearExpectations',

        self::SET_SCENARIO_STATE => 'createSetScenarioState',
        self::CLEAR_SCENARIOS    => 'createClearScenarios',

        self::COUNT_REQUESTS       => 'createCountRequests',
        self::LIST_REQUESTS        => 'createListRequests',
        self::RESET_REQUESTS_COUNT => 'createResetRequestsCount',

        self::RESET => 'createReset',

        self::MANAGE_REQUEST => 'createSearchRequest',
    ];

    /** @var ActionsFactory */
    private $factory;

    public function __construct(ActionsFactory $factory)
    {
        $this->factory = $factory;
    }

    public function locate(string $actionIdentifier): ActionInterface
    {
        if (\array_key_exists($actionIdentifier, self::ACTION_FACTORY_METHOD_MAP)) {
            return $this->factory->{self::ACTION_FACTORY_METHOD_MAP[$actionIdentifier]}();
        }
        throw new InvalidArgumentException(sprintf('Trying to get action using %s. Which is not a valid action name.', var_export($actionIdentifier, true)));
    }
}
