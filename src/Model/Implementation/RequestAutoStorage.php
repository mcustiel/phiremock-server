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

namespace Mcustiel\Phiremock\Server\Model\Implementation;

use Laminas\Diactoros\ServerRequest;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Psr\Http\Message\ServerRequestInterface;

class RequestAutoStorage implements RequestStorage
{
    /** @var ServerRequestInterface[] */
    private $requests;

    /** @var int|null */
    private $maxRequests;

    public function __construct(?int $maxRequests = null)
    {
        $this->maxRequests = $maxRequests === null ? null : max(1, $maxRequests);
        $this->clearRequests();
    }

    public function addRequest(ServerRequestInterface $request): void
    {
        $this->requests[] = $this->createRequestSnapshot($request);
        if ($this->maxRequests !== null && \count($this->requests) > $this->maxRequests) {
            $this->requests = array_slice($this->requests, -$this->maxRequests);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\RequestStorage::listRequests()
     */
    public function listRequests(): array
    {
        return $this->requests;
    }

    public function clearRequests(): void
    {
        $this->requests = [];
    }

    private function createRequestSnapshot(ServerRequestInterface $request): ServerRequestInterface
    {
        return new ServerRequest(
            [],
            [],
            $request->getUri(),
            $request->getMethod(),
            new StringStream($request->getBody()->__toString()),
            $request->getHeaders(),
            $request->getCookieParams(),
            $request->getQueryParams(),
            $request->getParsedBody(),
            $request->getProtocolVersion()
        );
    }
}
