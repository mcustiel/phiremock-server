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

class SameJsonCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendPOST('/__phiremock/reset');
    }

    public function shouldCompareJsonEvenIfStringsDiffer(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => '{"tomato": "potato", "a": 1, "b": null, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ])
        );

        $I->sendPOST('/test-json', '{"tomato" : "potato",   "a":1,    "b": null, "recursive": {   "a": "b", "array" : [ {"c":"d" }, "e" ] } }');
        $I->seeResponseCodeIs(200);
        $responseBody = $I->grabResponse();
        $I->assertEquals('It is the same', $responseBody);
    }

    public function shouldCompareJsonAndDetectTheyAreTheSameWhenFieldsOrderedDifferent(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => '{"tomato": "potato", "a": 1, "b": null, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ])
        );

        $I->sendPOST('/test-json', '{"b": null, "a":1,    "recursive": {   "array" : [ {"c":"d" }, "e" ], "a": "b" }, "tomato" : "potato" }');
        $I->seeResponseCodeIs(200);
        $responseBody = $I->grabResponse();
        $I->assertEquals('It is the same', $responseBody);
    }

    public function shouldCompareJsonAndDetectTheyAreNotTheSame(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => '{"tomato": "potato", "a": 1, "b": null, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ])
        );

        $I->sendPOST('/test-json', '{"tomato": "potato", "a": 1, "b": 0, "recursive": {"a": "b", "array": [{"c": "d"}, "e"]}}');
        $I->seeResponseCodeIs(404);
    }

    // From issue #38
    public function shouldDetectTheyAreNotTheSame(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => '{ "foo": "1", "bar": "2"}'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ])
        );
        $I->sendPOST('/test-json', '{ "foo": "1"}');
        $I->seeResponseCodeIs(404);
    }

    public function shouldFailIfConfiguredWithInvalidJson(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json-object'],
                    'body'   => ['isSameJsonObject' => 'I, am an invalid - json. string.'],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ])
        );

        $I->seeResponseCodeIs(500);
        $responseBody = $I->grabResponse();
        $I->assertStringStartsWith('{"result" : "ERROR", "details" : ["Invalid json: ', $responseBody);
    }

    public function shouldNotFailIfReceivesInvalidJsonInRequest(AcceptanceTester $I)
    {
        $json = [
            'tomato'    => 'potato',
            'a'         => 1,
            'b'         => null,
            'recursive' => [
                'a'     => 'b',
                'array' => [
                    ['c' => 'd'],
                    'e',
                ],
            ],
        ];
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'method' => 'post',
                    'url'    => ['isEqualTo' => '/test-json'],
                    'body'   => ['isSameJsonObject' => json_encode($json)],
                ],
                'response' => [
                    'body' => 'It is the same',
                ],
            ])
        );

        $I->sendPOST(
            '/test-json-object',
            'I, am an invalid - json. string.'
        );

        $I->seeResponseCodeIs(404);
    }
}
