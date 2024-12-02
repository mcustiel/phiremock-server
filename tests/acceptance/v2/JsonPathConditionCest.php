<?php

namespace Mcustiel\Phiremock\Server\Tests\V2;

use AcceptanceTester;

class JsonPathConditionCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function checkSimpleJsonPathCondition(AcceptanceTester $I)
    {
        $I->wantTo('verify that json path condition works');
        $I->haveHttpHeader('Content-Type', 'application/json');
        
        // Create expectation with json path condition
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'version' => '2',
                'on' => [
                    'method' => ['isSameString' => 'post'],
                    'url' => ['isEqualTo' => '/test-json-path'],
                    'jsonPath' => [
                        'user.address.zipCode' => [
                            'isEqualTo' => '12345',
                        ],
                    ]
                ],
                'then' => [
                    'response' => [
                        'statusCode' => 201,
                        'body' => 'Path matched'
                    ]
                ]
            ]
        );

        // Check expectation is successfully installed
        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('[{"version":"2","scenarioName":null,"on":{"scenarioStateIs":null,"method":{"isSameString":"post"},"url":{"isEqualTo":"\/test-json-path"},"body":null,"headers":null,"formData":null,"jsonPath":{"user.address.zipCode":{"isEqualTo":"12345"}}},"then":{"delayMillis":null,"newScenarioState":null,"proxyTo":null,"response":{"statusCode":201,"body":"Path matched","headers":null}},"priority":0}]');

        // Should match when json path value equals expected
        $I->sendPOST('/test-json-path', [
            'user' => [
                'name' => 'John',
                'address' => [
                    'street' => 'Main St',
                    'zipCode' => '12345'
                ]
            ]
        ]);
        $I->seeResponseCodeIs(201);
        $I->seeResponseEquals('Path matched');
        
        // Should not match when path exists but value is different
        $I->sendPOST('/test-json-path', [
            'user' => [
                'name' => 'John',
                'address' => [
                    'street' => 'Main St', 
                    'zipCode' => '54321'
                ]
            ]
        ]);
        $I->seeResponseCodeIs(404);

        // Should not match when path doesn't exist
        $I->sendPOST('/test-json-path', [
            'user' => [
                'name' => 'John',
                'address' => [
                    'street' => 'Main St'
                ]
            ]
        ]);
        $I->seeResponseCodeIs(404);
    }

    public function checkMultipleJsonPathConditions(AcceptanceTester $I)
    {
        $I->wantTo('verify that multiple json path conditions work together');
        $I->haveHttpHeader('Content-Type', 'application/json');
        
        // Create expectation with multiple json path conditions
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'version' => '2',
                'on' => [
                    'method' => ['isSameString' => 'post'],
                    'url' => ['isEqualTo' => '/test-json-path'],
                    'jsonPath' => [
                        'user.id' => [
                            'isEqualTo' => '123'
                        ],
                        'user.address.type' => [
                            'matches' => '/^(home|work)$/'
                        ],
                        'user.active' => [
                            'isEqualTo' => true
                        ]
                    ]
                ],
                'then' => [
                    'response' => [
                        'statusCode' => 201,
                        'body' => 'Multiple paths matched'
                    ]
                ]
            ]
        );

        // Check expectation is successfully installed
        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('[{"version":"2","scenarioName":null,"on":{"scenarioStateIs":null,"method":{"isSameString":"post"},"url":{"isEqualTo":"\/test-json-path"},"body":null,"headers":null,"formData":null,"jsonPath":{"user.id":{"isEqualTo":"123"},"user.address.type":{"matches":"\/^(home|work)$\/"},"user.active":{"isEqualTo":true}}},"then":{"delayMillis":null,"newScenarioState":null,"proxyTo":null,"response":{"statusCode":201,"body":"Multiple paths matched","headers":null}},"priority":0}]');

        // Should match when all conditions are met
        $I->sendPOST('/test-json-path', [
            'user' => [
                'id' => '123',
                'address' => [
                    'type' => 'home',
                    'street' => 'Main St'
                ],
                'active' => true
            ]
        ]);
        $I->seeResponseCodeIs(201);
        $I->seeResponseEquals('Multiple paths matched');

        // Should not match when one condition fails
        $I->sendPOST('/test-json-path', [
            'user' => [
                'id' => '123',
                'address' => [
                    'type' => 'apartment', // This won't match the regex
                    'street' => 'Main St'
                ],
                'active' => true
            ]
        ]);
        $I->seeResponseCodeIs(404);

        // Should not match when multiple conditions fail
        $I->sendPOST('/test-json-path', [
            'user' => [
                'id' => '456', // Wrong ID
                'address' => [
                    'type' => 'apartment', // Wrong type
                    'street' => 'Main St'
                ],
                'active' => false // Wrong active status
            ]
        ]);
        $I->seeResponseCodeIs(404);
    }

    public function checkJsonPathWithDifferentMatchers(AcceptanceTester $I)
    {
        $I->wantTo('verify that json path condition works with different matchers');
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Test with matches matcher
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'version' => '2',
                'on' => [
                    'method' => ['isSameString' => 'post'],
                    'url' => ['isEqualTo' => '/test-json-path'],
                    'jsonPath' => [
                        'data.code' => [
                            'matches' => '/^ABC-\d+$/'
                        ]
                    ]
                ],
                'then' => [
                    'response' => [
                        'statusCode' => 201,
                        'body' => 'Regex matched'
                    ]
                ]
            ]
        );

        // Check expectation is successfully installed
        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('[{"version":"2","scenarioName":null,"on":{"scenarioStateIs":null,"method":{"isSameString":"post"},"url":{"isEqualTo":"\/test-json-path"},"body":null,"headers":null,"formData":null,"jsonPath":{"data.code":{"matches":"\/^ABC-\\\\d+$\/"}}},"then":{"delayMillis":null,"newScenarioState":null,"proxyTo":null,"response":{"statusCode":201,"body":"Regex matched","headers":null}},"priority":0}]');

        // Checking expectation itself
        $I->sendPOST('/test-json-path', [
            'data' => [
                'code' => 'ABC-123'
            ]
        ]);
        $I->seeResponseCodeIs(201);
        $I->seeResponseEquals('Regex matched');

        $I->sendPOST('/test-json-path', [
            'data' => [
                'code' => 'XYZ-123'
            ]
        ]);
        $I->seeResponseCodeIs(404);
    }

    public function checkInvalidJsonPathConfiguration(AcceptanceTester $I)
    {
        $I->wantTo('verify that invalid json path configurations are handled properly');
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Missing path
        $I->sendPOST(
            '/__phiremock/expectations',
            [
                'version' => '2',
                'on' => [
                    'jsonPath' => [
                        'isEqualTo' => '123'
                    ]
                ],
                'then' => [
                    'response' => [
                        'statusCode' => 201
                    ]
                ]
            ]
        );
        $I->seeResponseCodeIs(500);
        $I->seeResponseContains('Json path condition is invalid');
    }
}
