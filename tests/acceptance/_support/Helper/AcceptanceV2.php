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

namespace Helper;

class AcceptanceV2 extends \Codeception\Module
{
    public function getPhiremockRequest(array $request): array
    {
        if (isset($request['request']['method'])) {
            $request['request']['method'] = ['isSameString' => $request['request']['method']];
        }
        if (\array_key_exists('request', $request)) {
            $request['on'] = $request['request'];
            unset($request['request']);
        }
        if (\array_key_exists('response', $request)) {
            $request['then'] = [
                'response' => $request['response'],
            ];
            unset($request['response']);
            if (\is_array($request['then']['response'])) {
                if (\array_key_exists('delayMillis', $request['then']['response'])) {
                    $request['then']['delayMillis'] = $request['then']['response']['delayMillis'];
                    unset($request['then']['response']['delayMillis']);
                }
            }
        }
        if (\array_key_exists('scenarioStateIs', $request)) {
            if (!isset($request['on'])) {
                $request['on'] = [];
            }
            $request['on']['scenarioStateIs'] = $request['scenarioStateIs'];
            unset($request['scenarioStateIs']);
        }
        if (\array_key_exists('newScenarioState', $request)) {
            if (!isset($request['then'])) {
                $request['then'] = [];
            }
            $request['then']['newScenarioState'] = $request['newScenarioState'];
            unset($request['newScenarioState']);
        }
        if (\array_key_exists('delayMillis', $request)) {
            if (!isset($request['then'])) {
                $request['then'] = [];
            }
            $request['then']['delayMillis'] = $request['delayMillis'];
            unset($request['delayMillis']);
        }
        if (\array_key_exists('proxyTo', $request)) {
            if (!isset($request['then'])) {
                $request['then'] = [];
            }
            $request['then']['proxyTo'] = $request['proxyTo'];
            unset($request['proxyTo']);
        }

        return array_merge(['version' => '2'], $request);
    }

    public function getPhiremockResponse(string $jsonResponse): string
    {
        $parsedExpectations = json_decode($jsonResponse, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            return $jsonResponse;
        }
        $v2 = [];
        foreach ($parsedExpectations as $parsedExpectation) {
            $v2Expectation = ['version' => '2'];
            if (\array_key_exists('scenarioName', $parsedExpectation)) {
                $v2Expectation['scenarioName'] = $parsedExpectation['scenarioName'];
            }
            if (\array_key_exists('scenarioStateIs', $parsedExpectation)) {
                $v2Expectation['on'] = [
                    'scenarioStateIs' => $parsedExpectation['scenarioStateIs'],
                ];
            }
            if (\array_key_exists('request', $parsedExpectation)) {
                if (isset($parsedExpectation['request']['method'])) {
                    $parsedExpectation['request']['method'] = ['isSameString' => $parsedExpectation['request']['method']];
                }
                if (!isset($v2Expectation['on'])) {
                    $v2Expectation['on'] = [];
                }
                $v2Expectation['on'] = array_merge($v2Expectation['on'], $parsedExpectation['request']);
            }
            $v2Expectation['then'] = [
                'delayMillis' => null,
            ];
            if (\is_array($parsedExpectation['response'])) {
                if (\array_key_exists('delayMillis', $parsedExpectation['response'])) {
                    $v2Expectation['then'] = [
                        'delayMillis' => $parsedExpectation['response']['delayMillis'],
                    ];
                    unset($parsedExpectation['response']['delayMillis']);
                }
            }
            if (\array_key_exists('newScenarioState', $parsedExpectation)) {
                if (!isset($v2Expectation['then'])) {
                    $v2Expectation['then'] = [];
                }
                $v2Expectation['then']['newScenarioState'] = $parsedExpectation['newScenarioState'];
            }
            if (\array_key_exists('proxyTo', $parsedExpectation)) {
                if (!isset($v2Expectation['then'])) {
                    $v2Expectation['then'] = [];
                }
                $v2Expectation['then']['proxyTo'] = $parsedExpectation['proxyTo'];
            }
            if (\array_key_exists('response', $parsedExpectation)) {
                if (!isset($v2Expectation['then'])) {
                    $v2Expectation['then'] = [];
                }
                $v2Expectation['then']['response'] = $parsedExpectation['response'];
            }
            if (\array_key_exists('priority', $parsedExpectation)) {
                $v2Expectation['priority'] = $parsedExpectation['priority'];
            }

            $v2[] = $v2Expectation;
        }

        return json_encode($v2);
    }
}
