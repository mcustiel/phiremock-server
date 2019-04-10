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

namespace Mcustiel\Phiremock\Server\Utils\Strategies;

use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Domain\ScenarioStateInfo;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class AbstractResponse
{
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    /** @var ScenarioStorage */
    private $scenariosStorage;

    public function __construct(ScenarioStorage $scenarioStorage, LoggerInterface $logger)
    {
        $this->scenariosStorage = $scenarioStorage;
        $this->logger = $logger;
    }

    /**
     * @param Response $responseConfig
     */
    protected function processDelay(Response $responseConfig)
    {
        if ($responseConfig->getDelayMillis()) {
            $this->logger->debug(
                'Delaying the response for ' . $responseConfig->getDelayMillis()->saInt . ' milliseconds'
            );
            usleep($responseConfig->getDelayMillis() * 1000);
        }
    }

    /**
     * @param MockConfig $foundExpectation
     */
    protected function processScenario(MockConfig $foundExpectation)
    {
        if ($foundExpectation->getResponse()->getNewScenarioState()) {
            if (!$foundExpectation->getStateConditions()->getScenarioName()) {
                throw new \RuntimeException(
                    'Expecting scenario state without specifying scenario name'
                );
            }
            $this->scenariosStorage->setScenarioState(
                new ScenarioStateInfo(
                    $foundExpectation->getStateConditions()->getScenarioName(),
                    $foundExpectation->getResponse()->getNewScenarioState()
                )
            );
        }
    }

    /**
     * @param Response          $responseConfig
     * @param ResponseInterface $httpResponse
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponseWithHeaders(HttpResponse $responseConfig, ResponseInterface $httpResponse)
    {
        if ($responseConfig->getHeaders()) {
            /** @var \Mcustiel\Phiremock\Domain\Http\Header $header */
            foreach ($responseConfig->getHeaders() as $header) {
                $httpResponse = $httpResponse->withHeader(
                    $header->getName()->asString(),
                    $header->getValue()->asString()
                );
            }
        }

        return $httpResponse;
    }

    /**
     * @param Response          $responseConfig
     * @param ResponseInterface $httpResponse
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponseWithStatusCode(HttpResponse $responseConfig, ResponseInterface $httpResponse)
    {
        if ($responseConfig->getStatusCode()) {
            $httpResponse = $httpResponse->withStatus($responseConfig->getStatusCode()->asInt());
        }

        return $httpResponse;
    }
}
