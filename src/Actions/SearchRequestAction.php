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

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Model\RequestStorage;
use Mcustiel\Phiremock\Server\Utils\RequestExpectationComparator;
use Mcustiel\Phiremock\Server\Utils\ResponseStrategyLocator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class SearchRequestAction implements ActionInterface
{
    /** @var ExpectationStorage */
    private $expectationsStorage;
    /** @var RequestExpectationComparator */
    private $comparator;
    /** @var LoggerInterface */
    private $logger;
    /** @var ResponseStrategyLocator */
    private $responseStrategyFactory;
    /** @var RequestStorage */
    private $requestsStorage;

    public function __construct(
        ExpectationStorage $expectationsStorage,
        RequestExpectationComparator $comparator,
        ResponseStrategyLocator $responseStrategyLocator,
        RequestStorage $requestsStorage,
        LoggerInterface $logger
    ) {
        $this->expectationsStorage = $expectationsStorage;
        $this->comparator = $comparator;
        $this->logger = $logger;
        $this->requestsStorage = $requestsStorage;
        $this->responseStrategyFactory = $responseStrategyLocator;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->logger->info('Request received: ' . $this->getLoggableRequest($request));
        $this->requestsStorage->addRequest($request);
        $foundExpectation = $this->searchForMatchingExpectation($request);
        if (null === $foundExpectation) {
            return $response->withStatus(404, 'Not Found');
        }
        $response = $this->responseStrategyFactory
            ->getStrategyForExpectation($foundExpectation)
            ->createResponse($foundExpectation, $response, $request);
        $this->logger->debug('Responding: ' . $this->getLoggableResponse($response));

        return $response;
    }

    private function searchForMatchingExpectation(ServerRequestInterface $request): ?Expectation
    {
        $lastFound = null;
        foreach ($this->expectationsStorage->listExpectations() as $expectation) {
            $lastFound = $this->getNextMatchingExpectation($lastFound, $request, $expectation);
        }

        return $lastFound;
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation|null $lastFound
     *
     * @return \Mcustiel\Phiremock\Domain\Expectation
     */
    private function getNextMatchingExpectation($lastFound, ServerRequestInterface $request, Expectation $expectation)
    {
        if ($this->comparator->equals($request, $expectation)) {
            if (null === $lastFound || $expectation->getPriority() > $lastFound->getPriority()) {
                $lastFound = $expectation;
            }
        }

        return $lastFound;
    }

    /**
     * @return string
     */
    private function getLoggableRequest(ServerRequestInterface $request)
    {
        $body = $request->getBody()->__toString();
        $longBody = '--VERY LONG CONTENTS--';
        $body = isset($body[2000]) ? $longBody : preg_replace('|\s+|', ' ', $body);

        return sprintf(
            '%s: %s || %s',
            $request->getMethod(),
            $request->getUri()->__toString(),
            $body
        );
    }

    private function getLoggableResponse(ResponseInterface $response): string
    {
        $body = $response->getBody()->__toString();

        return sprintf(
            '%d / %s',
            $response->getStatusCode(),
            isset($body[2000]) ? '--VERY LONG CONTENTS--' : preg_replace('|\s+|', ' ', $body)
        );
    }
}
