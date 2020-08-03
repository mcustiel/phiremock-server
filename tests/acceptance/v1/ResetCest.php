<?php

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
