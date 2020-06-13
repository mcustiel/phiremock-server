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
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Utils\RequestToExpectationMapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Mcustiel\Phiremock\Server\Utils\Traits\ExpectationValidator;

class CountRequestsAction implements ActionInterface
{
    use ExpectationValidator;

    /** @var \Mcustiel\Phiremock\Server\Model\RequestStorage */
    private $requestsStorage;
    /** @var \Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator */
    private $comparator;
    /** @var RequestToExpectationMapper */
    private $converter;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        RequestToExpectationMapper $converter,
        RequestStorage $storage,
        RequestExpectationComparator $comparator,
        LoggerInterface $logger
    ) {
        $this->requestsStorage = $storage;
        $this->comparator = $comparator;
        $this->converter = $converter;
        $this->logger = $logger;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($request->getBody()->__toString() === '') {
            $this->logger->info('Received empty body. Creating default');
            $request = $request->withBody(new StringStream('{"request": {"url": {"matches": "/.+/"}}, "response": {"statusCode": 200}}'));
        }
        $this->logger->debug('Adding Expectation->createObjectFromRequestAndProcess');
        $object = $this->converter->map($request);

        return $this->process($response, $object);
    }

    private function process(ResponseInterface $response, Expectation $expectation)
    {
        $this->validateRequestOrThrowException($expectation, $this->logger);
        $count = $this->searchForExecutionsCount($expectation);
        $this->logger->debug('Found ' . $count . ' request matching the expectation');

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new StringStream(json_encode(['count' => $count])));
    }

    /**
     * @return int
     */
    private function searchForExecutionsCount(Expectation $expectation)
    {
        $count = 0;
        foreach ($this->requestsStorage->listRequests() as $request) {
            if ($this->comparator->equals($request, $expectation)) {
                ++$count;
            }
        }

        return $count;
    }
}
