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

use Exception;
use Mcustiel\Phiremock\Common\Utils\ArrayToExpectationConverterLocator;
use Mcustiel\Phiremock\Domain\Expectation;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RequestToExpectationMapper
{
    const CONTENT_ENCODING_HEADER = 'Content-Encoding';

    /** @var ArrayToExpectationConverterLocator */
    private $converterLocator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ArrayToExpectationConverterLocator $converterLocator,
        LoggerInterface $logger
    ) {
        $this->converterLocator = $converterLocator;
        $this->logger = $logger;
    }

    /** @throws Exception */
    public function map(ServerRequestInterface $request): Expectation
    {
        $parsedJson = $this->parseJsonBody($request);
        $object = $this->converterLocator->locate($parsedJson)->convert($parsedJson);
        $this->logger->debug('Parsed expectation: ' . var_export($object, true));

        return $object;
    }

    /** @throws Exception */
    private function parseJsonBody(ServerRequestInterface $request): array
    {
        $this->logger->debug('Adding Expectation->parseJsonBody');
        $body = $request->getBody()->__toString();
        $this->logger->debug($body);
        if ($this->hasBinaryBody($request)) {
            $body = base64_decode($body, true);
        }

        $bodyJson = @json_decode($body, true);
        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception(json_last_error_msg());
        }
        $this->logger->debug('BODY JSON: ' . var_export($bodyJson, true));

        return $bodyJson;
    }

    private function hasBinaryBody(ServerRequestInterface $request): bool
    {
        return $request->hasHeader(self::CONTENT_ENCODING_HEADER)
            && 'base64' === $request->getHeader(self::CONTENT_ENCODING_HEADER);
    }
}
