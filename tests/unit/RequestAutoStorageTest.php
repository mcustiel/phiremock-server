<?php

use Laminas\Diactoros\ServerRequest;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Server\Model\Implementation\RequestAutoStorage;
use PHPUnit\Framework\TestCase;

class RequestAutoStorageTest extends TestCase
{
    public function testItKeepsAllRequestsWhenNoLimitIsConfigured(): void
    {
        $storage = new RequestAutoStorage();

        $storage->addRequest($this->request('/first'));
        $storage->addRequest($this->request('/second'));
        $storage->addRequest($this->request('/third'));

        $requests = $storage->listRequests();

        $this->assertCount(3, $requests);
        $this->assertSame('/first', $requests[0]->getUri()->getPath());
        $this->assertSame('/second', $requests[1]->getUri()->getPath());
        $this->assertSame('/third', $requests[2]->getUri()->getPath());
    }

    public function testItKeepsOnlyNewestRequestsWithinConfiguredLimit(): void
    {
        $storage = new RequestAutoStorage(2);

        $storage->addRequest($this->request('/first'));
        $storage->addRequest($this->request('/second'));
        $storage->addRequest($this->request('/third'));

        $requests = $storage->listRequests();

        $this->assertCount(2, $requests);
        $this->assertSame('/second', $requests[0]->getUri()->getPath());
        $this->assertSame('/third', $requests[1]->getUri()->getPath());
    }

    public function testItStoresRequestSnapshotWithRawBodyAndParsedBody(): void
    {
        $storage = new RequestAutoStorage(2);
        $request = $this->request('/form', 'name=potato%20raw')
            ->withParsedBody(['name' => 'potato raw']);

        $storage->addRequest($request);

        $storedRequest = $storage->listRequests()[0];

        $this->assertNotSame($request, $storedRequest);
        $this->assertSame('name=potato%20raw', (string) $storedRequest->getBody());
        $this->assertSame(['name' => 'potato raw'], $storedRequest->getParsedBody());
        $this->assertSame([], $storedRequest->getServerParams());
        $this->assertSame([], $storedRequest->getUploadedFiles());
    }

    private function request(string $path, string $body = ''): ServerRequest
    {
        return new ServerRequest([], [], $path, 'POST', new StringStream($body));
    }
}
