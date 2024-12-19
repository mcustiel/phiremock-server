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
use Codeception\Configuration;

class BinaryContentCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->sendDELETE('/__phiremock/expectations');
    }

    public function shouldCreateAnExpectationWithBinaryResponse(AcceptanceTester $I)
    {
        $responseContents = file_get_contents(Configuration::dataDir() . 'fixtures/silhouette-1444982_640.png');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['isEqualTo' => '/show-me-the-image-now'],
                ],
                'response' => [
                    'headers' => [
                        'Content-Type'     => 'image/jpeg',
                    ],
                    'body' => 'phiremock.base64:' . base64_encode($responseContents),
                ],
            ])
        );

        $I->sendGET('/show-me-the-image-now');
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Content-Type', 'image/jpeg');
        $responseBody = $I->grabResponse();
        $I->assertEquals($responseContents, $responseBody);
    }

    public function shouldCreateAnExpectationWithBinaryResponseAndRegexUrl(AcceptanceTester $I)
    {
        $responseContents = file_get_contents(Configuration::dataDir() . 'fixtures/silhouette-1444982_640.png');

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                'request' => [
                    'url' => ['matches' => '/\/show-me-the-image-\d+/'],
                ],
                'response' => [
                    'headers' => [
                        'Content-Type'     => 'image/jpeg',
                    ],
                    'body' => 'phiremock.base64:' . base64_encode($responseContents),
                ],
            ])
        );

        $I->sendGET('/show-me-the-image-123');
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Content-Type', 'image/jpeg');
        $responseBody = $I->grabResponse();
        $I->assertEquals($responseContents, $responseBody);
    }
}
