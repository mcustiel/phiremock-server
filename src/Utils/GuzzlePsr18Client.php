<?php

namespace Mcustiel\Phiremock\Server\Utils;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzlePsr18Client implements ClientInterface
{
    /** @var GuzzleClient */
    private $client;

    public function __construct(GuzzleClient $client = null)
    {
        $this->client = $client ?? new GuzzleClient(['allow_redirects' => false]);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->send($request);
    }
}
