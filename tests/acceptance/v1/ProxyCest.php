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
use GuzzleHttp\Client as HttpClient;

class ProxyCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createAnExpectationWithProxyToTest(AcceptanceTester $I)
    {
        $I->wantTo('create a specification that checks url using matches');
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'scenarioName'    => 'PotatoScenario',
                'scenarioStateIs' => 'Scenario.START',
                'request'         => [
                    'method'  => 'post',
                    'url'     => ['isEqualTo' => '/potato'],
                    'body'    => ['isEqualTo' => '{"key": "This is the body"}'],
                    'headers' => ['X-Potato' => ['isSameString' => 'bAnaNa']],
                ],
                'proxyTo' => 'https://www.w3schools.com/html/',
            ])
        );

        $I->sendGET('/__phiremock/expectations');
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson();
        $I->seeResponseEquals($I->getPhiremockResponse(
            '[{"scenarioName":"PotatoScenario","scenarioStateIs":"Scenario.START",'
            . '"newScenarioState":null,"request":{"method":"post","url":{"isEqualTo":"\/potato"},'
            . '"body":{"isEqualTo":"{\"key\": \"This is the body\"}"},"headers":{"X-Potato":'
            . '{"isSameString":"bAnaNa"}},"formData":null},"response":null,'
            . '"proxyTo":"https:\/\/www.w3schools.com\/html\/","priority":0}]'
        ));
    }

    public function proxyToGivenUriTest(AcceptanceTester $I)
    {
        $realUrl = 'http://info.cern.ch/';

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'scenarioName'    => 'PotatoScenario',
                'scenarioStateIs' => 'Scenario.START',
                'request'         => [
                    'url'     => ['isEqualTo' => '/potato'],
                    'headers' => ['X-Potato' => ['isSameString' => 'bAnaNa']],
                ],
                'proxyTo' => $realUrl,
            ])
        );

        $guzzle = new HttpClient();
        $originalBody = $guzzle->get($realUrl)->getBody()->__toString();

        $I->haveHttpHeader('X-Potato', 'banana');
        $I->sendGet('/potato');
        $I->seeResponseEquals($originalBody);
    }
}
