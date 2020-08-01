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

namespace Mcustiel\Phiremock\Server\Utils\Traits;

use Mcustiel\Phiremock\Domain\Conditions;
use Mcustiel\Phiremock\Domain\Expectation;
use Psr\Log\LoggerInterface;
use Mcustiel\Phiremock\Domain\HttpResponse;

trait ExpectationValidator
{
    /** @throws \RuntimeException */
    protected function validateExpectationOrThrowException(Expectation $expectation, LoggerInterface $logger)
    {
        $this->logger->debug('Adding Expectation->validateExpectationOrThrowException');
        $this->validateRequestOrThrowException($expectation, $logger);
        $this->logger->debug('Ran validateRequestOrThrowException');
        $this->validateResponseOrThrowException($expectation, $logger);
        $this->logger->debug('Ran validateResponseOrThrowException');
        $this->validateScenarioNameOrThrowException($expectation, $logger);
        $this->logger->debug('Ran validateScenarioNameOrThrowException');
        $this->validateScenarioStateOrThrowException($expectation, $logger);
        $this->logger->debug('Ran validateScenarioStateOrThrowException');
    }

    /** @throws \RuntimeException */
    protected function validateResponseOrThrowException(Expectation $expectation, LoggerInterface $logger)
    {
        $this->logger->debug('Validating response');
        if ($this->responseIsInvalid($expectation)) {
            $logger->error('Invalid response specified in expectation');
            throw new \RuntimeException('Invalid response specified in expectation');
        }
    }

    /** @throws \RuntimeException */
    protected function validateRequestOrThrowException(Expectation $expectation, LoggerInterface $logger)
    {
        if ($this->requestIsInvalid($expectation->getRequest())) {
            $logger->error('Invalid request specified in expectation');
            throw new \RuntimeException('Invalid request specified in expectation');
        }
    }

    protected function responseIsInvalid(Expectation $expectation): bool
    {
        /** @var HttpResponse $response */
        $response = $expectation->getResponse();

        return $response->isHttpResponse() && empty($response->getStatusCode());
    }

    protected function requestIsInvalid(Conditions $request): bool
    {
        return empty($request->getBody()) && empty($request->getHeaders())
        && empty($request->getMethod()) && empty($request->getUrl());
    }

    /** @throws \RuntimeException */
    protected function validateScenarioStateOrThrowException(
        Expectation $expectation,
        LoggerInterface $logger
    ): void {
        if ($expectation->getResponse()->hasNewScenarioState() && !$expectation->getRequest()->hasScenarioState()) {
            $logger->error('Scenario states misconfiguration');
            throw new \RuntimeException('Trying to set scenario state without specifying scenario previous state');
        }
    }

    /** @throws \RuntimeException */
    protected function validateScenarioNameOrThrowException(
        Expectation $expectation,
        LoggerInterface $logger
    ): void {
        if (!$expectation->hasScenarioName()
            && ($expectation->getRequest()->hasScenarioState() || $expectation->getResponse()->hasNewScenarioState())
        ) {
            $logger->error('Scenario name related misconfiguration');
            throw new \RuntimeException('Expecting or trying to set scenario state without specifying scenario name');
        }
    }
}
