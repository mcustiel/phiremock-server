<?php

/**
 * This file is part of phiremock-codeception-extension.
 *
 * phiremock-codeception-extension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phiremock-codeception-extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phiremock-codeception-extension.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mcustiel\Phiremock\Server\Tests\V1;

use AcceptanceTester;

class ExpectationCreationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createCatchAllRequest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks url');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
        $I->sendGET('/it/does/not/matter');
        $I->seeResponseCodeIs(201);
        $I->sendPOST('/potato', '{"tomato": "banana"}');
        $I->seeResponseCodeIs(201);
    }

    public function creationWithOnlyValidUrlConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks url');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function creationWithOnlyValidMethodConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks method');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'post',
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":"post","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function creationWithOnlyValidBodyConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks body');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'body' => ['matches' => '~potato~'],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":{"matches":"~potato~"},"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function creationWithOnlyValidHeadersConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that only checks headers');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers' => ['Accept' => ['matches' => '~potato~']],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":null,"body":null,"headers":{"Accept":{"matches":"~potato~"}},"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function useDefaultWhenEmptyResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('When response is empty in request, default should be used');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'get',
                ],
                'response' => null,
            ])
        );
        $I->seeResponseCodeIs('201');

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":"get","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":200,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function creationFailWhenAnythingSentAsRequestTest(AcceptanceTester $I)
    {
        $I->wantTo('See if creation fails when anything sent as request');

        $expectation = [
            'response' => ['statusCode' => 200],
            'request'  => ['potato' => 'tomato'],
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $I->getPhiremockRequest($expectation));

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Unknown request conditions: array (\n  \'potato\' => \'tomato\',\n)"]}');
    }

    public function creationFailWhenAnythingSentAsResponseTest(AcceptanceTester $I)
    {
        $I->wantTo('See if creation fails when anything sent as response');

        $expectation = [
            'response' => 'response',
            'request'  => ['url' => ['isEqualTo' => '/tomato']],
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/__phiremock/expectations', $I->getPhiremockRequest($expectation));

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Invalid response definition: \'response\'"]}'
        );
    }

    public function creationWithAllOptionsFilledTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with all possible option filled');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method'  => 'get',
                    'url'     => ['isEqualTo' => '/the/request/url'],
                    'body'    => ['isEqualTo' => 'the body'],
                    'headers' => [
                        'Content-Type'         => ['matches' => '/json/'],
                        'Accepts'              => ['isEqualTo' => 'application/json'],
                        'X-Some-Random-Header' => ['isEqualTo' => 'random value'],
                    ],
                    'formData' => [
                        'name' => ['isEqualTo' => 'potato'],
                    ],
                ],
                'response' => [
                    'statusCode' => 201,
                    'body'       => 'Response body',
                    'headers'    => [
                        'X-Special-Header' => 'potato',
                        'Location'         => 'href://potato.tmt',
                    ],
                    'delayMillis' => 5000,
                ],
                'scenarioName'     => 'potato',
                'scenarioStateIs'  => 'tomato',
                'newScenarioState' => 'banana',
                'priority'         => 3,
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":"potato","scenarioStateIs":"tomato","newScenarioState":"banana",'
            . '"request":{'
            . '"method":"get","url":{"isEqualTo":"\/the\/request\/url"},'
            . '"body":{"isEqualTo":"the body"},'
            . '"headers":{'
            . '"Content-Type":{"matches":"\/json\/"},'
            . '"Accepts":{"isEqualTo":"application\/json"},'
            . '"X-Some-Random-Header":{"isEqualTo":"random value"}},'
            . '"formData":{'
            . '"name":{"isEqualTo":"potato"}}},'
            . '"response":{'
            . '"statusCode":201,"body":"Response body","headers":{'
            . '"X-Special-Header":"potato",'
            . '"Location":"href:\/\/potato.tmt"},'
            . '"delayMillis":5000},'
            . '"proxyTo":null,"priority":3}]'
        ));
    }
}
