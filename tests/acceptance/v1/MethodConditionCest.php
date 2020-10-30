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

class MethodConditionCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function createAnExpectationUsingMethodPost(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with method post');
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

    public function createAnExpectationUsingMethodGet(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with method get');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'get',
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
            . '"request":{"method":"get","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function createAnExpectationUsingMethodPut(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with method put');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'put',
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
            . '"request":{"method":"put","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function createAnExpectationUsingMethodDelete(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with method delete');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'delete',
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
            . '"request":{"method":"delete","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function createAnExpectationUsingMethodFetch(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with method fetch');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'fetch',
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
            . '"request":{"method":"fetch","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function createAnExpectationUsingMethodOptions(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with method options');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'options',
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
            . '"request":{"method":"options","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function createAnExpectationUsingMethodHead(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with method head');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'head',
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
            . '"request":{"method":"head","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function createAnExpectationUsingMethodPatch(AcceptanceTester $I)
    {
        $I->wantTo('create a specification with method patch');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'patch',
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
            . '"request":{"method":"patch","url":null,"body":null,"headers":null,"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }
}
