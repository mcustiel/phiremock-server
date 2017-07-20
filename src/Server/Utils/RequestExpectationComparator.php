<?php

namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\Request;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\PowerRoute\Common\Conditions\ClassArgumentObject;
use Mcustiel\PowerRoute\Common\Factories\InputSourceFactory;
use Mcustiel\PowerRoute\Common\Factories\MatcherFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RequestExpectationComparator
{
    /**
     * @var \Mcustiel\PowerRoute\Common\Factories\MatcherFactory
     */
    private $matcherFactory;
    /**
     * @var \Mcustiel\PowerRoute\Common\Factories\InputSourceFactory
     */
    private $inputSourceFactory;
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ScenarioStorage
     */
    private $scenarioStorage;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Mcustiel\PowerRoute\Common\Factories\MatcherFactory     $matcherFactory
     * @param \Mcustiel\PowerRoute\Common\Factories\InputSourceFactory $inputSourceFactory
     * @param \Mcustiel\Phiremock\Server\Model\ScenarioStorage         $scenarioStorage
     * @param \Psr\Log\LoggerInterface                                 $logger
     */
    public function __construct(
        MatcherFactory $matcherFactory,
        InputSourceFactory $inputSourceFactory,
        ScenarioStorage $scenarioStorage,
        LoggerInterface $logger
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->inputSourceFactory = $inputSourceFactory;
        $this->scenarioStorage = $scenarioStorage;
        $this->logger = $logger;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $httpRequest
     * @param \Mcustiel\Phiremock\Domain\Expectation   $expectation
     */
    public function equals(ServerRequestInterface $httpRequest, Expectation $expectation)
    {
        $this->logger->debug('Checking if request matches an expectation');

        if (!$this->isExpectedScenarioState($expectation)) {
            return false;
        }

        $expectedRequest = $expectation->getRequest();

        $atLeastOneExecution = $this->compareRequestParts($httpRequest, $expectedRequest);

        if ($atLeastOneExecution !== null && $expectedRequest->getHeaders()) {
            $this->logger->debug('Checking headers against expectation');

            return $this->requestHeadersMatchExpectation($httpRequest, $expectedRequest);
        }

        return (bool) $atLeastOneExecution;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $httpRequest
     * @param \Mcustiel\Phiremock\Domain\Request       $expectedRequest
     *
     * @return null|bool
     */
    private function compareRequestParts(ServerRequestInterface $httpRequest, Request $expectedRequest)
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
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     *
     * @return bool
     */
    private function isExpectedScenarioState(Expectation $expectation)
    {
        if ($expectation->getScenarioStateIs()) {
            $this->checkScenarioNameOrThrowException($expectation);
            $this->logger->debug('Checking scenario state again expectation');
            $scenarioState = $this->scenarioStorage->getScenarioState(
                $expectation->getScenarioName()
            );
            if ($expectation->getScenarioStateIs() !== $scenarioState) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     *
     * @throws \RuntimeException
     */
    private function checkScenarioNameOrThrowException(Expectation $expectation)
    {
        if (!$expectation->getScenarioName()) {
            throw new \RuntimeException(
                'Expecting scenario state without specifying scenario name'
            );
        }
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $httpRequest
     * @param \Mcustiel\Phiremock\Domain\Request       $expectedRequest
     *
     * @return unknown
     */
    private function requestMethodMatchesExpectation(
        ServerRequestInterface $httpRequest,
        Request $expectedRequest
    ) {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'method' => null,
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            Matchers::SAME_STRING => $expectedRequest->getMethod(),
        ]);

        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $httpRequest
     * @param \Mcustiel\Phiremock\Domain\Request       $expectedRequest
     *
     * @return unknown
     */
    private function requestUrlMatchesExpectation(
        ServerRequestInterface $httpRequest,
        Request $expectedRequest
    ) {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'url' => null,
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            $expectedRequest->getUrl()->getMatcher() => $expectedRequest->getUrl()->getValue(),
        ]);

        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $httpRequest
     * @param \Mcustiel\Phiremock\Domain\Request       $expectedRequest
     *
     * @return unknown
     */
    private function requestBodyMatchesExpectation(
        ServerRequestInterface $httpRequest,
        Request $expectedRequest
    ) {
        $inputSource = $this->inputSourceFactory->createFromConfig([
            'body' => null,
        ]);
        $matcher = $this->matcherFactory->createFromConfig([
            $expectedRequest->getBody()->getMatcher() => $expectedRequest->getBody()->getValue(),
        ]);

        return $this->evaluate($inputSource, $matcher, $httpRequest);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $httpRequest
     * @param \Mcustiel\Phiremock\Domain\Request       $expectedRequest
     *
     * @return bool
     */
    private function requestHeadersMatchExpectation(
        ServerRequestInterface $httpRequest,
        Request $expectedRequest
    ) {
        foreach ($expectedRequest->getHeaders() as $header => $headerCondition) {
            $inputSource = $this->inputSourceFactory->createFromConfig([
                'header' => $header,
            ]);
            $matcher = $this->matcherFactory->createFromConfig([
                $headerCondition->getMatcher() => $headerCondition->getValue(),
            ]);

            if (!$this->evaluate($inputSource, $matcher, $httpRequest)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Mcustiel\PowerRoute\Common\Conditions\ClassArgumentObject $inputSource
     * @param \Mcustiel\PowerRoute\Common\Conditions\ClassArgumentObject $matcher
     * @param \Psr\Http\Message\ServerRequestInterface                   $httpRequest
     *
     * @return unknown
     */
    private function evaluate(
        ClassArgumentObject $inputSource,
        ClassArgumentObject $matcher,
        ServerRequestInterface $httpRequest
    ) {
        return $matcher->getInstance()->match(
            $inputSource->getInstance()->getValue($httpRequest, $inputSource->getArgument()),
            $matcher->getArgument()
        );
    }
}
