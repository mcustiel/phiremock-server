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

    /** @var StreamInterface */
    private $body;

    public function __construct(ServerRequestInterface $serverRequestInterface)
    {
        $this->parent = $serverRequestInterface;
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
        return $this->parent->withAttribute($name, $value);
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return $this->parent->withUri($uri, $preserveHost);
    }

    public function getMethod()
    {
        return $this->parent->getMethod();
    }

    public function withoutHeader($name)
    {
        return $this->parent->withoutHeader($name);
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
        return $this->parent->withCookieParams($cookies);
    }

    public function getAttribute($name, $default = null)
    {
        return $this->parent->getAttribute($name, $default);
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        return $this->parent->withUploadedFiles($uploadedFiles);
    }

    public function withAddedHeader($name, $value)
    {
        return $this->parent->withAddedHeader($name, $value);
    }

    public function withQueryParams(array $query)
    {
        return $this->parent->withQueryParams($query);
    }

    public function withoutAttribute($name)
    {
        return $this->parent->withoutAttribute($name);
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
        return $this->parent->withRequestTarget($requestTarget);
    }

    public function withProtocolVersion($version)
    {
        return $this->parent->withProtocolVersion($version);
    }

    public function withHeader($name, $value)
    {
        return $this->parent->withHeader($name, $value);
    }

    public function withBody(StreamInterface $body)
    {
        return $this->parent->withBody($body);
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
        return $this->parent->withParsedBody($data);
    }

    public function getUploadedFiles()
    {
        return $this->parent->getUploadedFiles();
    }

    public function withMethod($method)
    {
        return $this->parent->withMethod($method);
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
}
