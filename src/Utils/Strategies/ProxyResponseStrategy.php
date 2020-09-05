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

use Laminas\Diactoros\Uri;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ProxyResponseStrategy extends AbstractResponse implements ResponseStrategyInterface
{
    /** @var ClientInterface */
    private $httpService;

    public function __construct(
        ClientInterface $httpService,
        ScenarioStorage $scenarioStorage,
        LoggerInterface $logger
    ) {
        parent::__construct($scenarioStorage, $logger);
        $this->httpService = $httpService;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Utils\Strategies\ResponseStrategyInterface::createResponse()
     */
    public function createResponse(
        Expectation $expectation,
        ResponseInterface $transactionData,
        ServerRequestInterface $request
    ): ResponseInterface {
        /** @var \Mcustiel\Phiremock\Domain\ProxyResponse $response */
        $response = $expectation->getResponse();
        $url = $response->getUri()->asString();
        $this->logger->debug('Proxying request to : ' . $url);
        $this->processScenario($expectation);
        $this->processDelay($response);

        return $this->httpService->sendRequest(
            $request->withUri(new Uri($url))
        );
    }
}
