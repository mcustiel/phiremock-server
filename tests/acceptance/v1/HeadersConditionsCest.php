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

class HeadersConditionsCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function creationWithOneHeaderUsingEqualToTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks one header using isEqualTo');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers' => ['Content-Type' => ['isEqualTo' => 'application/x-www-form-urlencoded']],
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
            . '"request":{"method":null,"url":null,"body":null,"headers":{"Content-Type":{"isEqualTo":"application\/x-www-form-urlencoded"}},"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function creationWithOneHeaderUsingMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks one header using matches');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers' => ['Content-Type' => ['matches' => '/application/']],
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
            . '"request":{"method":null,"url":null,"body":null,"headers":{"Content-Type":{"matches":"\/application\/"}},"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function failWhenUsingInvalidMatcherTest(AcceptanceTester $I)
    {
        $I->wantTo('fail when the matcher is invalid');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers' => ['Content-Type' => ['potato' => '/application/']],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition matcher specified: potato"]}');
    }

    public function failWhenUsingNullValueTest(AcceptanceTester $I)
    {
        $I->wantTo('fail when the value is null');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers' => ['Content-Type' => ['matches' => null]],
                ],
                'response' => [
                    'statusCode' => 201,
                ],
            ])
        );

        $I->seeResponseCodeIs(500);
        $I->seeResponseIsJson();
        $I->seeResponseEquals('{"result" : "ERROR", "details" : ["Invalid condition value. Expected string, got: NULL"]}');
    }

    public function creationWithMoreThanOneHeaderConditionTest(AcceptanceTester $I)
    {
        $I->wantTo('create an expectation that checks more than one header');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers' => [
                        'Content-Type'     => ['matches' => '/application/'],
                        'Content-Length'   => ['isEqualTo' => '25611'],
                        'Content-Encoding' => ['isSameString' => 'gzip'],
                    ],
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
            . '"request":{"method":null,"url":null,"body":null,"headers":{'
            . '"Content-Type":{"matches":"\/application\/"},'
            . '"Content-Length":{"isEqualTo":"25611"},'
            . '"Content-Encoding":{"isSameString":"gzip"}},"formData":null},'
            . '"response":{"statusCode":201,"body":null,"headers":null,"delayMillis":null},'
            . '"proxyTo":null,"priority":0}]'
        ));
    }

    public function responseExpectedWhenRequestOneHeaderMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in one request header works');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers' => [
                        'Content-Type' => ['isEqualTo' => 'application/x-www-form-urlencoded'],
                    ],
                ],
                'response' => [
                    'body' => 'Found',
                ],
            ])
        );

        $I->seeResponseCodeIs(201);
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGET('/dontcare');

        $I->seeResponseCodeIs(200);
        $I->seeResponseEquals('Found');
    }

    public function responseExpectedWhenSeveralHeadersMatchesTest(AcceptanceTester $I)
    {
        $I->wantTo('see if mocking based in several request headers works');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'headers' => [
                        'Content-Type' => ['isEqualTo' => 'application/x-www-form-urlencoded'],
                        'X-Potato'     => ['matches' => '/.*tomato.*/'],
                        'X-Tomato'     => ['isSameString' => 'PoTaTo'],
                    ],
                ],
                'response' => [
                    'body' => 'Found',
                ],
            ])
        );

        $I->seeResponseCodeIs(201);

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGET('/dontcare');

        $I->seeResponseCodeIs(404);

        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->haveHttpHeader('X-potato', 'a-tomato-0');
        $I->haveHttpHeader('X-tomato', 'potato');
        $I->sendGET('/dontcare');

        $I->seeResponseEquals('Found');
    }
}
