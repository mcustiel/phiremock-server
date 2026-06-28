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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class ServerRequestWithCachedBody implements ServerRequestInterface
{
    /** @var ServerRequestInterface */
    private $parent;

    /** @var StreamInterface|null */
    private $body;

    public function __construct(ServerRequestInterface $serverRequestInterface, ?StreamInterface $body = null)
    {
        $this->parent = $serverRequestInterface;
        $this->body = $body;
    }

    public function getBody()
    {
        if ($this->body === null) {
            $this->body = new StringStream($this->parent->getBody()->__toString());
        }

        return $this->body;
    }

    public function getCookieParams()
    {
        return $this->parent->getCookieParams();
    }

    public function withAttribute($name, $value)
    {
        return $this->withParent($this->parent->withAttribute($name, $value));
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return $this->withParent($this->parent->withUri($uri, $preserveHost));
    }

    public function getMethod()
    {
        return $this->parent->getMethod();
    }

    public function withoutHeader($name)
    {
        return $this->withParent($this->parent->withoutHeader($name));
    }

    public function getHeaderLine($name)
    {
        return $this->parent->getHeaderLine($name);
    }

    public function getHeader($name)
    {
        return $this->parent->getHeader($name);
    }

    public function withCookieParams(array $cookies)
    {
        return $this->withParent($this->parent->withCookieParams($cookies));
    }

    public function getAttribute($name, $default = null)
    {
        return $this->parent->getAttribute($name, $default);
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        return $this->withParent($this->parent->withUploadedFiles($uploadedFiles));
    }

    public function withAddedHeader($name, $value)
    {
        return $this->withParent($this->parent->withAddedHeader($name, $value));
    }

    public function withQueryParams(array $query)
    {
        return $this->withParent($this->parent->withQueryParams($query));
    }

    public function withoutAttribute($name)
    {
        return $this->withParent($this->parent->withoutAttribute($name));
    }

    public function hasHeader($name)
    {
        return $this->parent->hasHeader($name);
    }

    public function getAttributes()
    {
        return $this->parent->getAttributes();
    }

    public function getHeaders()
    {
        return $this->parent->getHeaders();
    }

    public function getRequestTarget()
    {
        return $this->parent->getRequestTarget();
    }

    public function withRequestTarget($requestTarget)
    {
        return $this->withParent($this->parent->withRequestTarget($requestTarget));
    }

    public function withProtocolVersion($version)
    {
        return $this->withParent($this->parent->withProtocolVersion($version));
    }

    public function withHeader($name, $value)
    {
        return $this->withParent($this->parent->withHeader($name, $value));
    }

    public function withBody(StreamInterface $body)
    {
        return $this->withParent($this->parent->withBody($body), $body);
    }

    public function getProtocolVersion()
    {
        return $this->parent->getProtocolVersion();
    }

    public function getParsedBody()
    {
        return $this->parent->getParsedBody();
    }

    public function withParsedBody($data)
    {
        return $this->withParent($this->parent->withParsedBody($data));
    }

    public function getUploadedFiles()
    {
        return $this->parent->getUploadedFiles();
    }

    public function withMethod($method)
    {
        return $this->withParent($this->parent->withMethod($method));
    }

    public function getServerParams()
    {
        return $this->parent->getServerParams();
    }

    public function getQueryParams()
    {
        return $this->parent->getQueryParams();
    }

    public function getUri()
    {
        return $this->parent->getUri();
    }

    private function withParent(ServerRequestInterface $parent, ?StreamInterface $body = null): self
    {
        return new self($parent, $body ?? $this->getBody());
    }
}
