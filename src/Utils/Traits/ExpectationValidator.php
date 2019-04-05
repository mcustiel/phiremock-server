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

use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Domain\RequestConditions;
use Mcustiel\Phiremock\Domain\Response;
use Psr\Log\LoggerInterface;

trait ExpectationValidator
{
    /**
     * @param MockConfig      $expectation
     * @param LoggerInterface $logger
     *
     * @throws \RuntimeException
     */
    protected function validateExpectationOrThrowException(MockConfig $expectation, LoggerInterface $logger)
    {
        $this->logger->debug('Adding Expectation->validateExpectationOrThrowException');
        $this->validateRequestOrThrowException($expectation, $logger);
        $this->validateResponseOrThrowException($expectation, $logger);
        $this->validateScenarioConfigOrThrowException($expectation, $logger);
    }

    /**
     * @param MockConfig      $expectation
     * @param LoggerInterface $logger
     *
     * @throws \RuntimeException
     */
    protected function validateResponseOrThrowException(MockConfig $expectation, LoggerInterface $logger)
    {
        if ($this->responseIsInvalid($expectation->getResponse())) {
            $logger->error('Invalid response specified in expectation');
            throw new \RuntimeException('Invalid response specified in expectation');
        }
    }

    /**
     * @param MockConfig      $expectation
     * @param LoggerInterface $logger
     *
     * @throws \RuntimeException
     */
    protected function validateRequestOrThrowException(MockConfig $expectation, LoggerInterface $logger)
    {
        if ($this->requestIsInvalid($expectation->getRequest())) {
            $logger->error('Invalid request specified in expectation');
            throw new \RuntimeException('Invalid request specified in expectation');
        }
    }

    /**
     * @param Response $response
     *
     * @return bool
     */
    protected function responseIsInvalid(Response $response)
    {
        return empty($response->getStatusCode());
    }

    /**
     * @param RequestConditions $request
     *
     * @return bool
     */
    protected function requestIsInvalid(RequestConditions $request)
    {
        return empty($request->getBody()) && empty($request->getHeaders())
        && empty($request->getMethod()) && empty($request->getUrl());
    }

    /**
     * @param MockConfig      $expectation
     * @param LoggerInterface $logger
     */
    protected function validateScenarioConfigOrThrowException(
        MockConfig $expectation,
        LoggerInterface $logger
    ) {
        $this->validateScenarioNameOrThrowException($expectation, $logger);
        $this->validateScenarioStateOrThrowException($expectation, $logger);
    }

    /**
     * @param MockConfig      $expectation
     * @param LoggerInterface $logger
     *
     * @throws \RuntimeException
     */
    protected function validateScenarioStateOrThrowException(
        MockConfig $expectation,
        LoggerInterface $logger
    ) {
        if ($expectation->getResponse()->getNewScenarioState() && !$expectation->getStateConditions()->getScenarioStateIs()) {
            $logger->error('Scenario states misconfiguration');
            throw new \RuntimeException(
                'Trying to set scenario state without specifying scenario previous state'
            );
        }
    }

    /**
     * @param MockConfig      $expectation
     * @param LoggerInterface $logger
     *
     * @throws \RuntimeException
     */
    protected function validateScenarioNameOrThrowException(
        MockConfig $expectation,
        LoggerInterface $logger
    ) {
        if (!$expectation->getStateConditions()->getScenarioName()
            && ($expectation->getStateConditions()->getScenarioStateIs() || $expectation->getStateConditions()->getNewScenarioState())
        ) {
            $logger->error('Scenario name related misconfiguration');
            throw new \RuntimeException(
                'Expecting or trying to set scenario state without specifying scenario name'
            );
        }
    }
}
