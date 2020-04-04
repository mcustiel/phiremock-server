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
use Mcustiel\Phiremock\Server\Config\InputSources;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegexResponseStrategy extends AbstractResponse implements ResponseStrategyInterface
{
    public function createResponse(
        Expectation $expectation,
        ResponseInterface $httpResponse,
        ServerRequestInterface $request
    ): ResponseInterface {
        $responseConfig = $expectation->getResponse();
        $httpResponse = $this->getResponseWithBody(
            $expectation,
            $httpResponse,
            $request
        );
        $httpResponse = $this->getResponseWithStatusCode($responseConfig, $httpResponse);
        $httpResponse = $this->getResponseWithHeaders($responseConfig, $httpResponse);
        $this->processScenario($expectation);
        $this->processDelay($responseConfig);

        return $httpResponse;
    }

    private function getResponseWithBody(
        Expectation $expectation,
        ResponseInterface $httpResponse,
        ServerRequestInterface $httpRequest
    ): ResponseInterface {
        $responseBody = $expectation->getResponse()->getBody();
        if ($responseBody) {
            $bodyString = $responseBody->asString();
            $bodyString = $this->fillWithUrlMatches($expectation, $httpRequest, $bodyString);
            $bodyString = $this->fillWithBodyMatches($expectation, $httpRequest, $bodyString);
            $httpResponse = $httpResponse->withBody(new StringStream($bodyString));
        }

        return $httpResponse;
    }

    private function fillWithBodyMatches(
        Expectation $expectation,
        ServerRequestInterface $httpRequest,
        string $responseBody
    ): string {
        if ($this->bodyConditionIsRegex($expectation)) {
            return $this->replaceMatches(
                InputSources::BODY,
                $expectation->getRequest()->getBody()->getValue()->asString(),
                $httpRequest->getBody()->__toString(),
                $responseBody
            );
        }

        return $responseBody;
    }

    private function bodyConditionIsRegex(Expectation $expectation): bool
    {
        return $expectation->getRequest()->getBody()
            && Matchers::MATCHES === $expectation->getRequest()->getBody()->getMatcher()->asString();
    }

    private function fillWithUrlMatches(
        Expectation $expectation,
        ServerRequestInterface $httpRequest,
        string $responseBody
    ): string {
        if ($this->urlConditionIsRegex($expectation)) {
            return $this->replaceMatches(
                InputSources::URL,
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
            && Matchers::MATCHES === $expectation->getRequest()->getUrl()->getMatcher()->asString();
    }

    private function replaceMatches(
        string $type, string $pattern, string $subject, string $responseBody): string
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
            $responseBody = $this->replaceMatchesInBody($matches, $type, $responseBody);
        }

        return $responseBody;
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
