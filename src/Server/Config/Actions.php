<?php

namespace Mcustiel\Phiremock\Server\Config;

class Actions
{
    const ADD_EXPECTATION = 'addExpectation';
    const LIST_EXPECTATIONS = 'listExpectations';
    const CLEAR_EXPECTATIONS = 'clearExpectations';
    const SERVER_ERROR = 'serverError';
    const CLEAR_SCENARIOS = 'clearScenarios';
    const CHECK_EXPECTATIONS = 'checkExpectations';
    const VERIFY_EXPECTATIONS = 'verifyExpectations';
    const COUNT_REQUESTS = 'countRequests';
    const RESET_COUNT = 'resetCount';
    const STORE_REQUEST = 'storeRequest';
}
