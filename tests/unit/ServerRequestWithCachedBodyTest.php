<?php

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Server\Http\Implementation\ServerRequestWithCachedBody;
use PHPUnit\Framework\TestCase;

class ServerRequestWithCachedBodyTest extends TestCase
{
    public function testBodyCacheSurvivesRequestMutation(): void
    {
        $request = new ServerRequest([], [], '/original', 'POST', new StringStream('cached-body'));
        $cachedRequest = new ServerRequestWithCachedBody($request);

        $this->assertSame('cached-body', (string) $cachedRequest->getBody());

        $mutatedRequest = $cachedRequest->withUri(new Uri('http://example.com/proxy'));

        $this->assertInstanceOf(ServerRequestWithCachedBody::class, $mutatedRequest);
        $this->assertSame('/proxy', $mutatedRequest->getUri()->getPath());
        $this->assertSame('cached-body', (string) $mutatedRequest->getBody());
    }

    public function testWithBodyReplacesCachedBody(): void
    {
        $request = new ServerRequest([], [], '/original', 'POST', new StringStream('original-body'));
        $cachedRequest = new ServerRequestWithCachedBody($request);

        $mutatedRequest = $cachedRequest->withBody(new StringStream('replacement-body'));

        $this->assertInstanceOf(ServerRequestWithCachedBody::class, $mutatedRequest);
        $this->assertSame('replacement-body', (string) $mutatedRequest->getBody());
    }
}
