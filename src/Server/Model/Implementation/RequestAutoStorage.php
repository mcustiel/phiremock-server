<?php

namespace Mcustiel\Phiremock\Server\Model\Implementation;

use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Psr\Http\Message\ServerRequestInterface;

class RequestAutoStorage implements RequestStorage
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface[]
     */
    private $requests;

    public function __construct()
    {
        $this->clearRequests();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\RequestStorage::addRequest()
     */
    public function addRequest(ServerRequestInterface $request)
    {
        $this->requests[] = $request;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\RequestStorage::listRequests()
     */
    public function listRequests()
    {
        return $this->requests;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\Phiremock\Server\Model\RequestStorage::clearRequests()
     */
    public function clearRequests()
    {
        $this->requests = [];
    }
}
