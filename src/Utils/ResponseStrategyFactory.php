<?php

namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Mcustiel\Phiremock\Server\Factory\Factory;

class ResponseStrategyFactory
{
    /** @var Factory */
    private $factory;

    /** @param Factory $dependencyService */
    public function __construct(Factory $dependencyService)
    {
        $this->factory = $dependencyService;
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     *
     * @return \Mcustiel\Phiremock\Server\Utils\Strategies\ResponseStrategyInterface
     */
    public function getStrategyForExpectation(Expectation $expectation)
    {
        if (!empty($expectation->getProxyTo())) {
            return $this->factory->createProxyResponseStrategy();
        }
        if ($this->requestBodyOrUrlAreRegexp($expectation)) {
            return $this->factory->createRegexResponseStrategy();
        }

        return $this->factory->createHttpResponseStrategy();
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     *
     * @return bool
     */
    private function requestBodyOrUrlAreRegexp(Expectation $expectation)
    {
        return $expectation->getRequest()->getBody()
            && Matchers::MATCHES === $expectation->getRequest()->getBody()->getMatcher()
            || $expectation->getRequest()->getUrl()
            && Matchers::MATCHES === $expectation->getRequest()->getUrl()->getMatcher();
    }
}
