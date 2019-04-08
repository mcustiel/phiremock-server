<?php

namespace Mcustiel\Phiremock\Server\Actions;

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
    const RELOAD_EXPECTATIONS = 'reloadExpectations';

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

        self::RELOAD_EXPECTATIONS => 'createReloadPreconfiguredExpectations',

        self::MANAGE_REQUEST => 'createSearchRequest',
    ];

    /** @var ActionsFactory */
    private $factory;

    public function __construct(ActionsFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $actionIdentifier
     *
     * @throws \InvalidArgumentException
     *
     * @return ActionInterface
     */
    public function locate($actionIdentifier)
    {
        if (array_key_exists($actionIdentifier, self::ACTION_FACTORY_METHOD_MAP)) {
            return $this->factory->{self::ACTION_FACTORY_METHOD_MAP[$actionIdentifier]}();
        }
        throw new \InvalidArgumentException(
            sprintf(
                'Trying to get action using %s. Which is not a valid action name.',
                var_export($actionIdentifier, true)
            )
        );
    }
}
