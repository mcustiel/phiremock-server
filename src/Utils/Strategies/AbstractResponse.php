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

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Domain\Response;
use Mcustiel\Phiremock\Domain\ScenarioStateInfo;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AbstractResponse
{
    /** @var LoggerInterface */
    protected $logger;
    /** @var ScenarioStorage */
    private $scenariosStorage;

    public function __construct(ScenarioStorage $scenarioStorage, LoggerInterface $logger)
    {
        $this->scenariosStorage = $scenarioStorage;
        $this->logger = $logger;
    }

    protected function processDelay(Response $responseConfig): void
    {
        if ($responseConfig->getDelayMillis()) {
            $this->logger->debug(
                'Delaying the response for ' . $responseConfig->getDelayMillis()->asInt() . ' milliseconds'
            );
            usleep($responseConfig->getDelayMillis()->asInt() * 1000);
        }
    }

    protected function processScenario(Expectation $foundExpectation): void
    {
        if ($foundExpectation->getResponse()->hasNewScenarioState()) {
            if (!$foundExpectation->hasScenarioName()) {
                throw new RuntimeException('Expecting scenario state without specifying scenario name');
            }
            $this->logger->debug(
                sprintf(
                    'Setting scenario %s to %s',
                    $foundExpectation->getScenarioName()->asString(),
                    $foundExpectation->getResponse()->getNewScenarioState()->asString()
                )
            );
            $this->scenariosStorage->setScenarioState(
                new ScenarioStateInfo(
                    $foundExpectation->getScenarioName(),
                    $foundExpectation->getResponse()->getNewScenarioState()
                )
            );
        }
    }

    protected function getResponseWithHeaders(HttpResponse $responseConfig, ResponseInterface $httpResponse): ResponseInterface
    {
        if ($responseConfig->getHeaders()) {
            foreach ($responseConfig->getHeaders() as $header) {
                $httpResponse = $httpResponse->withHeader(
                    $header->getName()->asString(),
                    $header->getValue()->asString()
                );
            }
        }

        return $httpResponse;
    }

    protected function getResponseWithStatusCode(HttpResponse $responseConfig, ResponseInterface $httpResponse): ResponseInterface
    {
        if ($responseConfig->getStatusCode()) {
            $httpResponse = $httpResponse->withStatus($responseConfig->getStatusCode()->asInt());
        }

        return $httpResponse;
    }
}
