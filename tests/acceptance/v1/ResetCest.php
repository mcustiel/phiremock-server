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

class ResetCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
        $I->sendDELETE('/__phiremock/executions');
    }

    public function restoreExpectationAfterDelete(AcceptanceTester $I)
    {
        $I->sendPOST('/__phiremock/reset');

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');
    }

    public function restoreExpectationAfterRewrite(AcceptanceTester $I)
    {
        $I->sendPOST('/__phiremock/reset');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'get',
                    'url'    => ['isEqualTo' => '/hello'],
                ],
                'response' => [
                    'statusCode' => 200,
                    'body'       => 'Bye!',
                ],
                'priority' => 1,
            ])
        );

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Bye!');

        $I->sendPOST('/__phiremock/reset');

        $I->sendGET('/hello');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('Hello!');
    }

    public function resetRequestsCount(AcceptanceTester $I)
    {
        $I->sendPOST('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":0}');

        $I->sendGET('/the/request/url');

        $I->sendPOST('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":1}');

        $I->sendPOST('/__phiremock/reset');

        $I->sendPOST('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('{"count":0}');
    }

    public function clearRequestsList(AcceptanceTester $I)
    {
        $I->sendPUT('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('[]');

        $I->sendGET('/the/request/url');

        $I->sendPUT('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('[{"method":"GET","url":"http:\/\/localhost:8086\/the\/request\/url","headers":{"Host":["localhost:8086"],"User-Agent":["Symfony BrowserKit"],"Referer":["http:\/\/localhost:8086\/__phiremock\/executions"]},"cookies":[],"body":""}]');

        $I->sendPOST('/__phiremock/reset');

        $I->sendPUT('/__phiremock/executions', '');
        $I->seeResponseCodeIs('200');
        $I->seeResponseEquals('[]');
    }
}
