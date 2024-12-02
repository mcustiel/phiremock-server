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

class DelaySpecificationCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    // tests
    public function createExpectationWhithValidDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a valid delay specification');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'delayMillis' => 5000,
                ],
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":null,"scenarioStateIs":null,"newScenarioState":null,'
            . '"request":{"method":null,"url":{"isEqualTo":"\/the\/request\/url"},"body":null,"headers":null,"formData":null,"jsonPath":null},'
            . '"response":{"statusCode":200,"body":null,"headers":null,"delayMillis":5000},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function failWhithNegativedDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with a negative delay specification');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'delayMillis' => -5000,
                ],
            ])
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Delay must be greater or equal to 0. Got: -5000"]}'
        );
    }

    public function failWhithInvalidDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation with an invalid delay specification');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'delayMillis' => 'potato',
                ],
            ])
        );

        $I->seeResponseCodeIs('500');
        $I->seeResponseIsJson();
        $I->seeResponseEquals(
            '{"result" : "ERROR", "details" : ["Delay must be an integer. Got: string"]}'
        );
    }

    // tests
    public function mockRequestWithDelayTest(AcceptanceTester $I)
    {
        $I->wantTo('mock a request with delay');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/the/request/url'],
                ],
                'response' => [
                    'delayMillis' => 2000,
                ],
            ])
        );

        $I->seeResponseCodeIs(201);

        $start = microtime(true);
        $I->sendGET('/the/request/url');
        $I->seeResponseCodeIs(200);
        $I->assertGreaterThan(2000, (microtime(true) - $start) * 1000);
    }
}
