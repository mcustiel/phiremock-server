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

class RequestListCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $I->sendDELETE('/__phiremock/executions');
    }

    public function returnEmptyList(AcceptanceTester $I)
    {
        $I->sendPUT('/__phiremock/executions');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('[]');
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

        $I->sendPUT('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseContainsJson(
            json_decode(
                '[{"method":"GET","url":"http:\/\/localhost:8086\/the\/request\/url","headers":{"Host":["localhost:8086"],"User-Agent":["Symfony BrowserKit"],"Content-Type":["application\/json"],"Referer":["http:\/\/localhost:8086\/__phiremock\/expectations"]},"cookies":[],"body":""}]',
                true
            )
        );
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

        $I->sendPUT('/__phiremock/executions', $I->getPhiremockRequest([
            'request' => [
                'url' => ['isEqualTo' => '/the/request/url'],
            ],
            'response' => [
                'statusCode' => 201,
            ],
        ]));
        $I->seeResponseCodeIs('200');
        $I->seeResponseIsJson('200');
        $I->seeResponseContainsJson(
            json_decode(
                '[{"method":"GET","url":"http:\/\/localhost:8086\/the\/request\/url","headers":{"Host":["localhost:8086"],"User-Agent":["Symfony BrowserKit"],"Content-Type":["application\/json"],"Referer":["http:\/\/localhost:8086\/__phiremock\/expectations"]},"cookies":[],"body":""}]',
                true
            )
        );
    }
}
