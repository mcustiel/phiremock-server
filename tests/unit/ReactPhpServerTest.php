<?php

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Server\Http\Implementation\ReactPhpServer;
use Mcustiel\Phiremock\Server\Http\Implementation\ServerRequestWithCachedBody;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;

class ReactPhpServerTest extends TestCase
{
    public function testParsedBodyDoesNotReplaceRawRequestBody(): void
    {
        $handler = new ReactPhpServerRequestHandlerSpy();
        $server = new ReactPhpServer($handler, new NullLogger());
        $rawBody = 'name=potato%20raw&other=value';
        $parsedBody = ['name' => 'potato raw', 'other' => 'value'];
        $request = (new ServerRequest(
            [],
            [],
            '/form',
            'POST',
            new StringStream($rawBody),
            ['Content-Type' => 'application/x-www-form-urlencoded']
        ))->withParsedBody($parsedBody);

        $onRequest = new ReflectionMethod($server, 'onRequest');
        $onRequest->invoke($server, $request);

        $this->assertInstanceOf(ServerRequestWithCachedBody::class, $handler->request);
        $this->assertSame($rawBody, (string) $handler->request->getBody());
        $this->assertSame($parsedBody, $handler->request->getParsedBody());
    }
}

class ReactPhpServerRequestHandlerSpy implements RequestHandlerInterface
{
    public ?ServerRequestInterface $request = null;

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        return new Response();
    }
}
