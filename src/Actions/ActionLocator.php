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
    public const LIST_EXPECTATIONS = 'listExpectations';
    public const ADD_EXPECTATION = 'addExpectation';
    public const CLEAR_EXPECTATIONS = 'clearExpectations';
    public const SET_SCENARIO_STATE = 'setScenarioState';
    public const CLEAR_SCENARIOS = 'clearScenarios';
    public const COUNT_REQUESTS = 'countRequests';
    public const LIST_REQUESTS = 'listRequests';
    public const RESET_REQUESTS_COUNT = 'resetRequestsCount';
    public const RESET = 'reset';
    public const GUI = 'gui';

    public const MANAGE_REQUEST = 'manageRequest';

    public const ACTION_FACTORY_METHOD_MAP = [
        self::LIST_EXPECTATIONS  => 'createListExpectations',
        self::ADD_EXPECTATION    => 'createAddExpectation',
        self::CLEAR_EXPECTATIONS => 'createClearExpectations',

        self::SET_SCENARIO_STATE => 'createSetScenarioState',
        self::CLEAR_SCENARIOS    => 'createClearScenarios',

        self::COUNT_REQUESTS       => 'createCountRequests',
        self::LIST_REQUESTS        => 'createListRequests',
        self::RESET_REQUESTS_COUNT => 'createResetRequestsCount',

        self::RESET => 'createReset',
        self::GUI   => 'createGui',

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
