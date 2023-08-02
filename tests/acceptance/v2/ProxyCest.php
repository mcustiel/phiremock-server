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

namespace Mcustiel\Phiremock\Server\Tests\V2;

use AcceptanceTester;
use GuzzleHttp\Client as HttpClient;
use Mcustiel\Phiremock\Server\Tests\V1\ProxyCest as ProxyCestV1;

class ProxyCest extends ProxyCestV1
{
    public function proxyToGivenUriUsingDataFromRequestTest(AcceptanceTester $I): void
    {
        $realUrl = 'http://info.cern.ch/hypertext/WWW/TheProject.html';

        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST(
            '/__phiremock/expectations',
            $I->getPhiremockRequest([
                                'version' => '2',
                'request'                 => [
                    'url'                    => ['matches' => '~^/path/([a-z]+)~i'],
                                      'body' => ['matches' => '~"file"\s*:\s*\"([a-z]+)"~i'],
                ],
                'proxyTo' => 'http://info.cern.ch/hypertext/${url.1}/${body.1}.html',
            ])
        );

        $guzzle = new HttpClient();
        $originalBody = $guzzle->get($realUrl)->getBody()->__toString();

        $I->sendPost('/path/WWW', ['file' => 'TheProject']);
        $I->seeResponseEquals($originalBody);
    }
}
