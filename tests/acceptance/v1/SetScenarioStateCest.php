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

class SetScenarioStateCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function setScenarioState(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'get',
                    'url'    => ['isEqualTo' => '/test'],
                ],
                'response' => [
                    'body' => 'start',
                ],
                'scenarioName'    => 'test-scenario',
                'scenarioStateIs' => 'Scenario.START',
            ])
        );

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'get',
                    'url'    => ['isEqualTo' => '/test'],
                ],
                'response' => [
                    'body' => 'potato',
                ],
                'scenarioName'    => 'test-scenario',
                'scenarioStateIs' => 'Scenario.POTATO',
            ])
         );

        $I->sendGET('/test');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('start');

        $I->sendPUT(
            '/__phiremock/scenarios',
            [
                'scenarioName'  => 'test-scenario',
                'scenarioState' => 'Scenario.POTATO',
            ]
        );
        $I->sendGET('/test');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('potato');

        $I->sendPUT(
            '/__phiremock/scenarios',
            [
                'scenarioName'  => 'test-scenario',
                'scenarioState' => 'Scenario.START',
            ]
        );
        $I->sendGET('/test');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('start');
    }

    public function checkScenarioStateValidation(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/__phiremock/scenarios', []);
        $I->seeResponseCodeIs(500);
        $I->seeResponseEquals('{"result":"ERROR","details":"Scenario name not set"}');
    }
}
