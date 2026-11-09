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

namespace Mcustiel\Phiremock\Server\Http\Implementation;

use Mcustiel\Phiremock\Common\StringStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestWithCachedBody implements ServerRequestInterface
{
    /** @var ServerRequestInterface */
    private $parent;

    /** @var StreamInterface */
    private $body;

    public function __construct(ServerRequestInterface $serverRequestInterface)
    {
        $this->parent = $serverRequestInterface;
    }

    public function getBody(): StreamInterface
    {
        if ($this->body === null) {
            $this->body = new StringStream($this->parent->getBody()->__toString());
        }

        return $this->body;
    }

    public function getCookieParams(): array
    {
        return $this->parent->getCookieParams();
    }

    public function withAttribute($name, $value): ServerRequestInterface
    {
        return $this->parent->withAttribute($name, $value);
    }

    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        return $this->parent->withUri($uri, $preserveHost);
    }

    public function getMethod(): string
    {
        return $this->parent->getMethod();
    }

    public function withoutHeader($name): MessageInterface
    {
        return $this->parent->withoutHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->parent->getHeaderLine($name);
    }

    public function getHeader($name): array
    {
        return $this->parent->getHeader($name);
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        return $this->parent->withCookieParams($cookies);
    }

    public function getAttribute($name, $default = null)
    {
        return $this->parent->getAttribute($name, $default);
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        return $this->parent->withUploadedFiles($uploadedFiles);
    }

    public function withAddedHeader($name, $value): MessageInterface
    {
        return $this->parent->withAddedHeader($name, $value);
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        return $this->parent->withQueryParams($query);
    }

    public function withoutAttribute($name): ServerRequestInterface
    {
        return $this->parent->withoutAttribute($name);
    }

    public function hasHeader($name): bool
    {
        return $this->parent->hasHeader($name);
    }

    public function getAttributes(): array
    {
        return $this->parent->getAttributes();
    }

    public function getHeaders(): array
    {
        return $this->parent->getHeaders();
    }

    public function getRequestTarget(): string
    {
        return $this->parent->getRequestTarget();
    }

    public function withRequestTarget($requestTarget): RequestInterface
    {
        return $this->parent->withRequestTarget($requestTarget);
    }

    public function withProtocolVersion($version): MessageInterface
    {
        return $this->parent->withProtocolVersion($version);
    }

    public function withHeader($name, $value): MessageInterface
    {
        return $this->parent->withHeader($name, $value);
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->parent->withBody($body);
    }

    public function getProtocolVersion(): string
    {
        return $this->parent->getProtocolVersion();
    }

    public function getParsedBody()
    {
        return $this->parent->getParsedBody();
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        return $this->parent->withParsedBody($data);
    }

    public function getUploadedFiles(): array
    {
        return $this->parent->getUploadedFiles();
    }

    public function withMethod($method): RequestInterface
    {
        return $this->parent->withMethod($method);
    }

    public function getServerParams(): array
    {
        return $this->parent->getServerParams();
    }

    public function getQueryParams(): array
    {
        return $this->parent->getQueryParams();
    }

    public function getUri(): UriInterface
    {
        return $this->parent->getUri();
    }
}
