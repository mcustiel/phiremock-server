<?php

namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Domain\MockConfig;
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
     * @param \Mcustiel\Phiremock\Domain\MockConfig $expectation
     *
     * @return \Mcustiel\Phiremock\Server\Utils\Strategies\ResponseStrategyInterface
     */
    public function getStrategyForExpectation(MockConfig $expectation)
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
     * @param \Mcustiel\Phiremock\Domain\MockConfig $expectation
     *
     * @return bool
     */
    private function requestBodyOrUrlAreRegexp(MockConfig $expectation)
    {
        return $expectation->getRequest()->getBody()
            && Matchers::MATCHES === $expectation->getRequest()->getBody()->getMatcher()
            || $expectation->getRequest()->getUrl()
            && Matchers::MATCHES === $expectation->getRequest()->getUrl()->getMatcher();
    }
}
