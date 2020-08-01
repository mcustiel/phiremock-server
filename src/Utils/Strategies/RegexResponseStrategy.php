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
use Mcustiel\Phiremock\Domain\Condition\MatchersEnum;
use Mcustiel\Phiremock\Domain\Expectation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegexResponseStrategy extends AbstractResponse implements ResponseStrategyInterface
{
    const PLACEHOLDER_BODY = 'body';
    const PLACEHOLDER_URL = 'url';

    public function createResponse(
        Expectation $expectation,
        ResponseInterface $httpResponse,
        ServerRequestInterface $request
    ): ResponseInterface {
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
        /** @var \Mcustiel\Phiremock\Domain\HttpResponse $responseConfig */
        $responseConfig = $expectation->getResponse();

        if ($responseConfig->hasBody()) {
            $bodyString = $responseConfig->getBody()->asString();
            $bodyString = $this->fillWithUrlMatches($expectation, $httpRequest, $bodyString);
            $bodyString = $this->fillWithBodyMatches($expectation, $httpRequest, $bodyString);
            $httpResponse = $httpResponse->withBody(new StringStream($bodyString));
        }

        return $httpResponse;
    }

    private function getResponseWithReplacedHeaders(
        Expectation $expectation,
        ResponseInterface $httpResponse,
        ServerRequestInterface $httpRequest
    ) {
        /** @var \Mcustiel\Phiremock\Domain\HttpResponse $responseConfig */
        $responseConfig = $expectation->getResponse();
        $headers = $responseConfig->getHeaders();

        if ($headers === null || $headers->isEmpty()) {
            return $httpResponse;
        }

        foreach ($headers as $header) {
            $headerValue = $header->getValue()->asString();
            $headerValue = $this->fillWithUrlMatches($expectation, $httpRequest, $headerValue);
            $headerValue = $this->fillWithBodyMatches($expectation, $httpRequest, $headerValue);
            $httpResponse = $httpResponse->withHeader($header->getName()->asString(), $headerValue);
        }

        return $httpResponse;
    }

    private function fillWithBodyMatches(
        Expectation $expectation,
        ServerRequestInterface $httpRequest,
        string $responseBody
    ): string {
        if ($this->bodyConditionIsRegex($expectation)) {
            $responseBody = $this->replaceMatches(
                self::PLACEHOLDER_BODY,
                $expectation->getRequest()->getBody()->getValue()->asString(),
                $httpRequest->getBody()->__toString(),
                $responseBody
            );
        }

        return $responseBody;
    }

    private function fillWithUrlMatches(
        Expectation $expectation,
        ServerRequestInterface $httpRequest,
        string $responseBody
    ): string {
        if ($this->urlConditionIsRegex($expectation)) {
            return $this->replaceMatches(
                self::PLACEHOLDER_URL,
                $expectation->getRequest()->getUrl()->getValue()->asString(),
                $this->getUri($httpRequest),
                $responseBody
            );
        }

        return $responseBody;
    }

    private function getUri(ServerRequestInterface $httpRequest): string
    {
        $path = ltrim($httpRequest->getUri()->getPath(), '/');
        $query = $httpRequest->getUri()->getQuery();
        $return = '/' . $path;
        if ($query) {
            $return .= '?' . $httpRequest->getUri()->getQuery();
        }

        return $return;
    }

    private function urlConditionIsRegex(Expectation $expectation): bool
    {
        return $expectation->getRequest()->getUrl()
            && MatchersEnum::MATCHES === $expectation->getRequest()->getUrl()->getMatcher()->getName();
    }

    private function bodyConditionIsRegex(Expectation $expectation): bool
    {
        return $expectation->getRequest()->getBody()
            && MatchersEnum::MATCHES === $expectation->getRequest()->getBody()->getMatcher()->getName();
    }

    private function replaceMatches(
        string $type, string $pattern, string $subject, string $destination): string
    {
        $matches = [];

        $matchCount = preg_match_all(
            $pattern,
            $subject,
            $matches
        );
        if ($matchCount > 0) {
            // we don't need full matches
            unset($matches[0]);
            $destination = $this->replaceMatchesInBody($matches, $type, $destination);
        }

        return $destination;
    }

    private function replaceMatchesInBody(array $matches, string $type, string $responseBody): string
    {
        $search = [];
        $replace = [];

        foreach ($matches as $matchGroupId => $matchGroup) {
            // add first element as replacement for $(type.index)
            $search[] = "\${{$type}.{$matchGroupId}}";
            $replace[] = reset($matchGroup);
            foreach ($matchGroup as $matchId => $match) {
                // fix index to start with 1 instead of 0
                ++$matchId;
                $search[] = "\${{$type}.{$matchGroupId}.{$matchId}}";
                $replace[] = $match;
            }
        }

        return str_replace($search, $replace, $responseBody);
    }
}
