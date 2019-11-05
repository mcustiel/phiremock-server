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
use Mcustiel\Phiremock\Common\Utils\ArrayToExpectationConverter;
use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\RequestToExpectationMapper;
use Mcustiel\Phiremock\Server\Utils\Traits\ExpectationValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class AddExpectationAction implements ActionInterface
{
    use ExpectationValidator;

    /** @var ExpectationStorage */
    private $storage;
    /** @var RequestToExpectationMapper */
    private $converter;
    /** @var LoggerInterface */
    private $logger;

    /**
     * @param ArrayToExpectationConverter $converter
     */
    public function __construct(
        RequestToExpectationMapper $converter,
        ExpectationStorage $storage,
        LoggerInterface $logger
    ) {
        $this->converter = $converter;
        $this->logger = $logger;
        $this->storage = $storage;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->logger->debug('Adding Expectation->execute');
        try {
            $object = $this->converter->map($request);

            return $this->process($response, $object);
        } catch (\Exception $e) {
            $this->logger->error('An unexpected exception occurred: ' . $e->getMessage());
            $this->logger->debug($e->__toString());

            return $this->constructErrorResponse([$e->getMessage()], $response);
        }
    }

    private function process(ResponseInterface $response, Expectation $expectation)
    {
        $this->logger->debug('process');
        $this->validateExpectationOrThrowException($expectation, $this->logger);
        $this->storage->addExpectation($expectation);

        return $this->constructResponse([], $response);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function constructResponse(array $listOfErrors, ResponseInterface $response)
    {
        $this->logger->debug('Adding Expectation->constructResponse');
        if (empty($listOfErrors)) {
            return $response->withStatus(201)->withBody(new StringStream('{"result" : "OK"}'));
        }

        return $this->constructErrorResponse($listOfErrors, $response);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function constructErrorResponse(array $listOfErrors, ResponseInterface $response)
    {
        $this->logger->debug('Adding Expectation->constructErrorResponse');

        return $response->withStatus(500)
            ->withBody(
                new StringStream(
                    '{"result" : "ERROR", "details" : '
                    . json_encode($listOfErrors)
                    . '}'
                )
            );
    }
}
