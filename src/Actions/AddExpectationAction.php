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
use Mcustiel\Phiremock\Domain\MockConfig;
use Mcustiel\Phiremock\Server\Model\ExpectationStorage;
use Mcustiel\Phiremock\Server\Utils\Traits\ExpectationValidator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class AddExpectationAction implements ActionInterface
{
    use ExpectationValidator;

    /** @var ExpectationStorage */
    private $storage;

    /** @var ArrayToExpectationConverter */
    private $converter;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param ArrayToExpectationConverter $converter
     * @param ExpectationStorage          $storage
     * @param LoggerInterface             $logger
     */
    public function __construct(
        ArrayToExpectationConverter $converter,
        ExpectationStorage $storage,
        LoggerInterface $logger
    ) {
        $this->converter = $converter;
        $this->logger = $logger;
        $this->storage = $storage;
    }

    public function execute(RequestInterface $request, ResponseInterface $response)
    {
        $this->logger->debug('Adding Expectation->execute');
        try {
            $this->logger->debug('Adding Expectation->processAndGetResponse');

            return $this->createObjectFromRequestAndProcess($request, $response);
        } catch (\Exception $e) {
            $this->logger->error('An unexpected exception occurred: ' . $e->getMessage());
            $this->logger->debug($e->__toString());

            return $this->constructErrorResponse([$e->getMessage()], $response);
        }
    }

    private function process(ResponseInterface $response, MockConfig $expectation)
    {
        $this->logger->debug('process');
        $this->validateExpectationOrThrowException($expectation, $this->logger);
        $this->storage->addExpectation($expectation);

        return $this->constructResponse([], $response);
    }

    /**
     * @param array                               $listOfErrors
     * @param \Psr\Http\Message\ResponseInterface $response
     *
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
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Mcustiel\Phiremock\Domain\MockConfig
     */
    private function parseRequestObject(ServerRequestInterface $request)
    {
        $this->logger->debug('Adding Expectation->parseRequestObject');
        /** @var \Mcustiel\Phiremock\Domain\MockConfig $object */
        $object = $this->converter->convert(
            $this->parseJsonBody($request)
        );
        $this->logger->debug(var_export($object, true));

        return $object;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Exception
     *
     * @return array
     */
    private function parseJsonBody(ServerRequestInterface $request)
    {
        $this->logger->debug('Adding Expectation->parseJsonBody');
        $body = $request->getBody()->__toString();
        $this->logger->debug($body);
        if ($request->hasHeader('Content-Encoding') && 'base64' === $request->getHeader('Content-Encoding')) {
            $body = base64_decode($body, true);
        }

        $bodyJson = @json_decode($body, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }
        $this->logger->debug(var_export($bodyJson, true));

        return $bodyJson;
    }

    private function createObjectFromRequestAndProcess(RequestInterface $request, ResponseInterface $response)
    {
        $this->logger->debug('Adding Expectation->createObjectFromRequestAndProcess');
        $object = $this->parseRequestObject($request);

        return $this->process($response, $object);
    }

    /**
     * @param array                               $listOfErrors
     * @param \Psr\Http\Message\ResponseInterface $response
     *
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
