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

use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Domain\RequestConditions;
use Mcustiel\Phiremock\Server\Config\InputSources;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Mcustiel\Phiremock\Server\Http\InputSources\InputSourceLocator;
use Mcustiel\Phiremock\Server\Http\Matchers\MatcherLocator;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RequestExpectationComparator
{
    /**
     * @var MatcherLocator
     */
    private $matcherLocator;
    /**
     * @var InputSourceLocator
     */
    private $inputSourceLocator;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ScenarioStorage
     */
    private $scenarioStorage;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        MatcherLocator $matcherLocator,
        InputSourceLocator $inputSourceLocator,
        ScenarioStorage $scenarioStorage,
        LoggerInterface $logger
    ) {
        $this->matcherLocator = $matcherLocator;
        $this->inputSourceLocator = $inputSourceLocator;
        $this->scenarioStorage = $scenarioStorage;
        $this->logger = $logger;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $httpRequest
     * @param \Mcustiel\Phiremock\Domain\MockConfig    $expectation
     *
     * @return bool
     */
    public function equals(ServerRequestInterface $httpRequest, MockConfig $expectation)
    {
        $this->logger->debug('Checking if request matches an expectation');

        if (!$this->isExpectedScenarioState($expectation)) {
            return false;
        }

        $expectedRequest = $expectation->getRequest();

        $atLeastOneExecution = $this->compareRequestParts($httpRequest, $expectedRequest);

        if (null !== $atLeastOneExecution && $expectedRequest->getHeaders()) {
            $this->logger->debug('Checking headers against expectation');

            return $this->requestHeadersMatchExpectation($httpRequest, $expectedRequest);
        }

        return (bool) $atLeastOneExecution;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface     $httpRequest
     * @param \Mcustiel\Phiremock\Domain\RequestConditions $expectedRequest
     *
     * @return null|bool
     */
    private function compareRequestParts(ServerRequestInterface $httpRequest, RequestConditions $expectedRequest)
    {
        $atLeastOneExecution = false;
        $requestParts = ['Method', 'Url', 'Body'];

        foreach ($requestParts as $requestPart) {
            $getter = "get{$requestPart}";
            $matcher = "request{$requestPart}MatchesExpectation";
            if ($expectedRequest->{$getter}()) {
                $this->logger->debug("Checking {$requestPart} against expectation");
                if (!$this->{$matcher}($httpRequest, $expectedRequest)) {
                    return null;
                }
                $atLeastOneExecution = true;
            }
        }

        return $atLeastOneExecution;
    }

    /**
     * @param MockConfig $expectation
     *
     * @return bool
     */
    private function isExpectedScenarioState(MockConfig $expectation)
    {
        if ($expectation->getStateConditions()->getScenarioStateIs() !== null) {
            $this->checkScenarioNameOrThrowException($expectation);
            $this->logger->debug('Checking scenario state again expectation');
            $scenarioState = $this->scenarioStorage->getScenarioState(
                $expectation->getStateConditions()->getScenarioName()
            );
            if (!$expectation->getStateConditions()->getScenarioStateIs()->equals($scenarioState)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param MockConfig $expectation
     *
     * @throws \RuntimeException
     */
    private function checkScenarioNameOrThrowException(MockConfig $expectation)
    {
        if ($expectation->getStateConditions()->getScenarioName() === null) {
            throw new \InvalidArgumentException(
                'Expecting scenario state without specifying scenario name'
            );
        }
    }

    /**
     * @param ServerRequestInterface $httpRequest
     * @param RequestConditions      $expectedRequest
     *
     * @return bool
     */
    private function requestMethodMatchesExpectation(ServerRequestInterface $httpRequest, RequestConditions $expectedRequest)
    {
        $inputSource = $this->inputSourceLocator->locate(InputSources::METHOD);
        $matcher = $this->matcherLocator->locate(Matchers::SAME_STRING);

        return $matcher->match(
            $inputSource->getValue($httpRequest),
            $expectedRequest->getMethod()->asString()
        );
    }

    /**
     * @param ServerRequestInterface $httpRequest
     * @param RequestConditions      $expectedRequest
     *
     * @return bool
     */
    private function requestUrlMatchesExpectation(ServerRequestInterface $httpRequest, RequestConditions $expectedRequest)
    {
        $inputSource = $this->inputSourceLocator->locate('url');
        $matcher = $this->matcherLocator->locate($expectedRequest->getUrl()->getMatcher()->asString());

        return $matcher->match(
            $inputSource->getValue($httpRequest),
            $expectedRequest->getUrl()->getValue()->asString()
        );
    }

    /**
     * @param ServerRequestInterface $httpRequest
     * @param RequestConditions      $expectedRequest
     *
     * @return bool
     */
    private function requestBodyMatchesExpectation(ServerRequestInterface $httpRequest, RequestConditions $expectedRequest)
    {
        $inputSource = $this->inputSourceLocator->locate('body');
        $matcher = $this->matcherLocator->locate(
            $expectedRequest->getBody()->getMatcher()->asString()
        );

        return $matcher->match(
            $inputSource->getValue($httpRequest),
            $expectedRequest->getBody()->getValue()->asString()
        );
    }

    /**
     * @param ServerRequestInterface $httpRequest
     * @param RequestConditions      $expectedRequest
     *
     * @return bool
     */
    private function requestHeadersMatchExpectation(ServerRequestInterface $httpRequest, RequestConditions $expectedRequest)
    {
        $inputSource = $this->inputSourceLocator->locate('header');
        foreach ($expectedRequest->getHeaders() as $header => $headerCondition) {
            $matcher = $this->matcherLocator->locate(
                $headerCondition->getMatcher()->asString()
            );

            $matches = $matcher->match(
                $inputSource->getValue($httpRequest, $header->asString()),
                $headerCondition->getValue()->asString()
            );
            if (!$matches) {
                return false;
            }
        }

        return true;
    }
}
