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

namespace Mcustiel\Phiremock\Server\Actions;

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Utils\RequestToMockConfigMapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ListRequestsAction implements ActionInterface
{
    /** @var \Mcustiel\Phiremock\Server\Model\RequestStorage */
    private $requestsStorage;
    /** @var \Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator */
    private $comparator;
    /** @var RequestToMockConfigMapper */
    private $converter;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        RequestToMockConfigMapper $converter,
        RequestStorage $storage,
        RequestExpectationComparator $comparator,
        LoggerInterface $logger
    ) {
        $this->requestsStorage = $storage;
        $this->comparator = $comparator;
        $this->converter = $converter;
        $this->logger = $logger;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response)
    {
        $object = $this->converter->map($request);

        return $this->process($response, $object);
    }

    public function process(ResponseInterface $response, MockConfig $expectation)
    {
        $this->validateRequestOrThrowException($expectation, $this->logger);
        $executions = $this->searchForExecutionsCount($expectation);
        $this->logger->debug('Listed ' . \count($executions) . ' request matching the expectation');

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StringStream(json_encode($executions)));
    }

    /**
     * @param MockConfig $expectation
     *
     * @return array[]
     */
    private function searchForExecutionsCount(MockConfig $expectation)
    {
        $executions = [];
        foreach ($this->requestsStorage->listRequests() as $request) {
            if ($this->comparator->equals($request, $expectation)) {
                $executions[] = [
                    'method'  => $request->getMethod(),
                    'url'     => (string) $request->getUri(),
                    'headers' => $request->getHeaders(),
                    'cookies' => $request->getCookieParams(),
                    'body'    => (string) $request->getBody(),
                ];
            }
        }

        return $executions;
    }
}
