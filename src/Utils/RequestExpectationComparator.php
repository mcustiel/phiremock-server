<?php
/**
 * This file is part of Phiremock.
 *
 * Phiremock is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Phiremock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Phiremock.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mcustiel\Phiremock\Server\Utils;

use InvalidArgumentException;
use Mcustiel\Phiremock\Domain\Conditions;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RequestExpectationComparator
{
    /** @var ScenarioStorage */
    private $scenarioStorage;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ScenarioStorage $scenarioStorage,
        LoggerInterface $logger
    ) {
        $this->scenarioStorage = $scenarioStorage;
        $this->logger = $logger;
    }

    public function equals(ServerRequestInterface $httpRequest, Expectation $expectation): bool
    {
        $this->logger->debug('Checking if request matches an expectation');

        if (!$this->isExpectedScenarioState($expectation)) {
            return false;
        }

        $expectedRequest = $expectation->getRequest();

        $foundMatch = $this->compareRequestParts($httpRequest, $expectedRequest);
        $this->logger->debug('Matches? ' . ((bool) $foundMatch ? 'yes' : 'no'));

        return $foundMatch;
    }

    private function compareRequestParts(ServerRequestInterface $httpRequest, Conditions $expectedRequest): bool
    {
        return $this->requestMethodMatchesExpectation($httpRequest, $expectedRequest)
            && $this->requestUrlMatchesExpectation($httpRequest, $expectedRequest)
            && $this->requestBodyMatchesExpectation($httpRequest, $expectedRequest)
            && $this->requestHeadersMatchExpectation($httpRequest, $expectedRequest)
            && $this->requestFormDataMatchExpectation($httpRequest, $expectedRequest)
            && $this->requestJsonMatchesExpectation($httpRequest, $expectedRequest);
    }

    private function isExpectedScenarioState(Expectation $expectation): bool
    {
        if ($expectation->getRequest()->hasScenarioState()) {
            $this->checkScenarioNameOrThrowException($expectation);
            $this->logger->debug('Checking scenario state again expectation');
            $scenarioState = $this->scenarioStorage->getScenarioState(
                $expectation->getScenarioName()
            );
            if (!$expectation->getRequest()->getScenarioState()->equals($scenarioState)) {
                return false;
            }
        }

        return true;
    }

    /** @throws InvalidArgumentException */
    private function checkScenarioNameOrThrowException(Expectation $expectation)
    {
        if (!$expectation->hasScenarioName()) {
            throw new InvalidArgumentException('Expecting scenario state without specifying scenario name');
        }
    }

    private function requestMethodMatchesExpectation(ServerRequestInterface $httpRequest, Conditions $expectedRequest): bool
    {
        $method = $expectedRequest->getMethod();
        if (!$method) {
            return true;
        }
        $this->logger->debug('Checking METHOD against expectation');

        return $method->getMatcher()->matches($httpRequest->getMethod());
    }

    private function requestUrlMatchesExpectation(ServerRequestInterface $httpRequest, Conditions $expectedRequest): bool
    {
        $url = $expectedRequest->getUrl();
        if (!$url) {
            return true;
        }
        $this->logger->debug('Checking URL against expectation');

        $requestUrl = $this->getComparableRequestUrl($httpRequest);

        return $url->getMatcher()->matches($requestUrl);
    }

    private function getComparableRequestUrl($httpRequest)
    {
        $requestUrl = $httpRequest->getUri()->getPath();
        if ($httpRequest->getUri()->getQuery()) {
            $requestUrl .= '?' . $httpRequest->getUri()->getQuery();
        }
        if ($httpRequest->getUri()->getFragment()) {
            $requestUrl .= '#' . $httpRequest->getUri()->getFragment();
        }

        return $requestUrl;
    }

    private function requestBodyMatchesExpectation(ServerRequestInterface $httpRequest, Conditions $expectedRequest): bool
    {
        $bodycondition = $expectedRequest->getBody();
        if (!$bodycondition) {
            return true;
        }
        $this->logger->debug('Checking BODY against expectation');

        return $bodycondition->getMatcher()->matches($httpRequest->getBody()->__toString());
    }

    private function requestHeadersMatchExpectation(ServerRequestInterface $httpRequest, Conditions $expectedRequest): bool
    {
        $headerConditions = $expectedRequest->getHeaders();
        if (!$headerConditions) {
            return true;
        }
        $this->logger->debug('Checking HEADERS against expectation');
        foreach ($headerConditions as $header => $headerCondition) {
            $headerName = $header->asString();
            $this->logger->debug("Checking $headerName against expectation");

            $matches = $headerCondition->getMatcher()->matches(
                $httpRequest->getHeaderLine($headerName)
            );
            if (!$matches) {
                return false;
            }
        }

        return true;
    }

    private function requestFormDataMatchExpectation(ServerRequestInterface $httpRequest, Conditions $expectedRequest): bool
    {
        $formDataConditions = $expectedRequest->getFormFields();
        if (!$formDataConditions) {
            return true;
        }
        $this->logger->debug('Checking FORM DATA against expectation');
        foreach ($formDataConditions as $field => $fieldCondition) {
            $fieldName = $field->asString();
            $this->logger->debug("Checking $fieldName against expectation");

            if (!isset($httpRequest->getParsedBody()[$fieldName])) {
                return false;
            }

            $matches = $fieldCondition->getMatcher()->matches(
                $httpRequest->getParsedBody()[$fieldName]
            );
            if (!$matches) {
                return false;
            }
        }

        return true;
    }

    private function requestJsonMatchesExpectation(ServerRequestInterface $httpRequest, Conditions $expectedRequest): bool
    {
        if (!$expectedRequest->hasJsonPath()) {
            return true;
        }
        
        $this->logger->debug('Checking JSON PATH against expectation');
        
        $requestBody = $httpRequest->getBody()->__toString();
        $requestData = json_decode($requestBody, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        /** @var JsonPathName $pathName */
        /** @var JsonPathCondition $jsonCondition */
        foreach ($expectedRequest->getJsonPath() as $pathName => $jsonCondition) {
            $path = explode('.', $pathName->asString());
            $value = $requestData;
            
            foreach ($path as $key) {
                if (!is_array($value) || !isset($value[$key])) {
                    return false;
                }
                $value = $value[$key];
            }
            
            if (!$jsonCondition->getMatcher()->matches($value)) {
                return false;
            }
        }
        
        return true;
    }
}
