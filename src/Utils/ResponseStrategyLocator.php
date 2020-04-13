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

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Mcustiel\Phiremock\Server\Factory\Factory;

class ResponseStrategyLocator
{
    /**
     * @var Factory
     */
    private $factory;

    public function __construct(Factory $dependencyService)
    {
        $this->factory = $dependencyService;
    }

    /**
     * @return \Mcustiel\Phiremock\Server\Utils\Strategies\ResponseStrategyInterface
     */
    public function getStrategyForExpectation(Expectation $expectation)
    {
        if ($expectation->getResponse()->isProxyResponse()) {
            return $this->factory->createProxyResponseStrategy();
        }
        if ($this->requestBodyOrUrlAreRegexp($expectation)) {
            return $this->factory->createRegexResponseStrategy();
        }

        return $this->factory->createHttpResponseStrategy();
    }

    /**
     * @return bool
     */
    private function requestBodyOrUrlAreRegexp(Expectation $expectation)
    {
        return $expectation->getRequest()->getBody()
            && Matchers::MATCHES === $expectation->getRequest()->getBody()->getMatcher()->getName()
            || $expectation->getRequest()->getUrl()
            && Matchers::MATCHES === $expectation->getRequest()->getUrl()->getMatcher()->getName();
    }
}
