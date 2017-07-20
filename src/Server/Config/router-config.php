<?php

use Mcustiel\Phiremock\Server\Config\Actions;
use Mcustiel\Phiremock\Server\Config\InputSources;
use Mcustiel\Phiremock\Server\Config\Matchers;

return [
    'start' => 'expectationUrl',
    'nodes' => [
// -------------------------------- API: expectations ---------------------------
        'expectationUrl' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => [InputSources::URL => 'path'],
                        'matcher'      => [
                            Matchers::MATCHES => '/\\_\\_phiremock\/expectations\/?$/',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['goto' => 'expectationMethodIsPost'],
                ],
                'else' => [
                    ['goto' => 'scenariosUrl'],
                ],
            ],
        ],
        'expectationMethodIsPost' => [
            'condition' => [
                'all-of' => [
                    [
                        'input-source' => [InputSources::METHOD => null],
                        'matcher'      => [Matchers::EQUAL_TO => 'POST'],
                    ],
                    [
                        'input-source' => [InputSources::HEADER => 'Content-Type'],
                        'matcher'      => [Matchers::EQUAL_TO=> 'application/json'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    [Actions::ADD_EXPECTATION => null],
                ],
                'else' => [
                    ['goto' => 'expectationMethodIsGet'],
                ],
            ],
        ],
        'expectationMethodIsGet' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => [InputSources::METHOD => null],
                        'matcher'      => [Matchers::EQUAL_TO => 'GET'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    [Actions::LIST_EXPECTATIONS => null],
                ],
                'else' => [
                    ['goto' => 'expectationMethodIsDelete'],
                ],
            ],
        ],
        'expectationMethodIsDelete' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => [InputSources::METHOD => null],
                        'matcher'      => [Matchers::EQUAL_TO => 'DELETE'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    [Actions::CLEAR_EXPECTATIONS => null],
                ],
                'else' => [
                    ['goto' => 'apiError'],
                ],
            ],
        ],

// -------------------------------- API: scenarios ---------------------------
        'scenariosUrl' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => [InputSources::URL => 'path'],
                        'matcher'      => [
                            Matchers::MATCHES => '/\\_\\_phiremock\/scenarios\/?$/',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['goto' => 'scenariosMethodIsDelete'],
                ],
                'else' => [
                    ['goto' => 'verifyUrl'],
                ],
            ],
        ],
        'scenariosMethodIsDelete' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => [InputSources::METHOD => null],
                        'matcher'      => [Matchers::EQUAL_TO => 'DELETE'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    [Actions::CLEAR_SCENARIOS => null],
                ],
                'else' => [
                    ['goto' => 'apiError'],
                ],
            ],
        ],

// -------------------------------- API: executions ---------------------------

        'verifyUrl' => [
            'condition' => [
                'one-of' => [
                    [
                        'input-source' => [InputSources::URL => 'path'],
                        'matcher'      => [
                            Matchers::MATCHES => '/\\_\\_phiremock\/executions\/?$/',
                        ],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    ['goto' => 'verifyMethodIsPost'],
                ],
                'else' => [
                    ['goto' => 'default'],
                ],
            ],
        ],
        'verifyMethodIsPost' => [
            'condition' => [
                'all-of' => [
                    [
                        'input-source' => [InputSources::METHOD => null],
                        'matcher'      => [Matchers::EQUAL_TO => 'POST'],
                    ],
                    [
                        'input-source' => [InputSources::HEADER => 'Content-Type'],
                        'matcher'      => [Matchers::EQUAL_TO => 'application/json'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    [Actions::COUNT_REQUESTS => null],
                ],
                'else' => [
                    ['goto' => 'verifyMethodIsDelete'],
                ],
            ],
        ],
        'verifyMethodIsDelete' => [
            'condition' => [
                'all-of' => [
                    [
                        'input-source' => [InputSources::METHOD => null],
                        'matcher'      => [Matchers::EQUAL_TO => 'DELETE'],
                    ],
                ],
            ],
            'actions' => [
                'if-matches' => [
                    [Actions::RESET_COUNT => null],
                ],
                'else' => [
                    ['goto' => 'apiError'],
                ],
            ],
        ],

// -------------------------------- API: error happened ---------------------------
        'apiError' => [
            'condition' => [],
            'actions'   => [
                'if-matches' => [
                    [Actions::SERVER_ERROR => null],
                ],
                'else' => [],
            ],
        ],

// ---------------------------- Verify configured expectations -----------------------
        'default' => [
            'condition' => [],
            'actions'   => [
                'if-matches' => [
                    [Actions::STORE_REQUEST => null],
                    [Actions::CHECK_EXPECTATIONS  => null],
                    [Actions::VERIFY_EXPECTATIONS => null],
                ],
                'else' => [],
            ],
        ],
    ],
];
