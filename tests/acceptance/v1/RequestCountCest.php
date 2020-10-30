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

class RequestCountCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $I->sendDELETE('/__phiremock/executions');
    }

    public function returnEmptyList(AcceptanceTester $I)
    {
        $I->sendPOST('/__phiremock/executions');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":0}');
    }

    public function returnAllExecutedRequest(AcceptanceTester $I)
    {
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

        $I->sendGET('/the/request/url');
        $I->seeResponseCodeIs('201');

        $I->sendPOST('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":1}');
    }

    public function returnExecutedRequestMatchingExpectation(AcceptanceTester $I)
    {
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

        $I->sendGET('/the/request/url');
        $I->seeResponseCodeIs('201');

        $I->sendPOST('/__phiremock/executions', $I->getPhiremockRequest([
            'request' => [
                'url' => ['isEqualTo' => '/the/request/url'],
            ],
            'response' => [
                'statusCode' => 201,
            ],
        ]));
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":1}');
    }
}
