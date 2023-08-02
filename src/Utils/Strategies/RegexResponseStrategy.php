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

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Domain\HttpResponse;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\Phiremock\Server\Utils\Strategies\Utils\RegexReplacer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RegexResponseStrategy extends AbstractResponse implements ResponseStrategyInterface
{
    /** @var RegexReplacer */
    private $regexReplacer;

    public function __construct(
        ScenarioStorage $scenarioStorage,
        LoggerInterface $logger,
        RegexReplacer $regexReplacer
    ) {
        parent::__construct($scenarioStorage, $logger);

        $this->regexReplacer = $regexReplacer;
    }

    public function createResponse(Expectation $expectation, ResponseInterface $httpResponse, ServerRequestInterface $request): ResponseInterface
    {
        $httpResponse = $this->getResponseWithReplacedBody(
            $expectation,
            $httpResponse,
            $request
        );
        $httpResponse = $this->getResponseWithReplacedHeaders(
            $expectation,
            $httpResponse,
            $request
        );
        /** @var HttpResponse $responseConfig */
        $responseConfig = $expectation->getResponse();
        $httpResponse = $this->getResponseWithStatusCode($responseConfig, $httpResponse);
        $this->processScenario($expectation);
        $this->processDelay($responseConfig);

        return $httpResponse;
    }

    private function getResponseWithReplacedBody(
        Expectation $expectation,
        ResponseInterface $httpResponse,
        ServerRequestInterface $httpRequest
    ): ResponseInterface {
        /** @var HttpResponse $responseConfig */
        $responseConfig = $expectation->getResponse();

        if ($responseConfig->hasBody()) {
            $bodyString = $responseConfig->getBody()->asString();
            $bodyString = $this->regexReplacer->fillWithUrlMatches($expectation, $httpRequest, $bodyString);
            $bodyString = $this->regexReplacer->fillWithBodyMatches($expectation, $httpRequest, $bodyString);
            $httpResponse = $httpResponse->withBody(new StringStream($bodyString));
        }

        return $httpResponse;
    }

    private function getResponseWithReplacedHeaders(
        Expectation $expectation,
        ResponseInterface $httpResponse,
        ServerRequestInterface $httpRequest
    ): ResponseInterface {
        /** @var HttpResponse $responseConfig */
        $responseConfig = $expectation->getResponse();
        $headers = $responseConfig->getHeaders();

        if ($headers === null || $headers->isEmpty()) {
            return $httpResponse;
        }

        foreach ($headers as $header) {
            $headerValue = $header->getValue()->asString();
            $headerValue = $this->regexReplacer->fillWithUrlMatches($expectation, $httpRequest, $headerValue);
            $headerValue = $this->regexReplacer->fillWithBodyMatches($expectation, $httpRequest, $headerValue);
            $httpResponse = $httpResponse->withHeader($header->getName()->asString(), $headerValue);
        }

        return $httpResponse;
    }
}
