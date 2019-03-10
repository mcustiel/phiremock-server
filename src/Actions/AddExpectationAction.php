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
use Mcustiel\Phiremock\Server\Utils\Traits\ExpectationValidator;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
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

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $this->logger->debug('Adding Expectation->execute');
        $transactionData->setResponse(
            $this->processAndGetResponse(
                $transactionData
            )
        );
    }

    private function process(TransactionData $transaction, Expectation $expectation)
    {
        $this->logger->debug('process');
        $this->validateExpectationOrThrowException($expectation, $this->logger);
        $this->storage->addExpectation($expectation);

        return $this->constructResponse([], $transaction->getResponse());
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
     * @param TransactionData $transactionData
     * @param callable        $process
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function processAndGetResponse(TransactionData $transactionData)
    {
        try {
            $this->logger->debug('Adding Expectation->processAndGetResponse');

            return $this->createObjectFromRequestAndProcess($transactionData);
        } catch (\Exception $e) {
            $this->logger->error('An unexpected exception occurred: ' . $e->getMessage());
            $this->logger->debug($e->__toString());

            return $this->constructErrorResponse([$e->getMessage()], $transactionData->getResponse());
        }
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Mcustiel\Phiremock\Domain\Expectation
     */
    private function parseRequestObject(ServerRequestInterface $request)
    {
        $this->logger->debug('Adding Expectation->parseRequestObject');
        /** @var \Mcustiel\Phiremock\Domain\Expectation $object */
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

    /**
     * @param TransactionData $transactionData
     * @param callable        $process
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function createObjectFromRequestAndProcess(TransactionData $transactionData)
    {
        $this->logger->debug('Adding Expectation->createObjectFromRequestAndProcess');
        $object = $this->parseRequestObject($transactionData->getRequest());

        return $this->process($transactionData, $object);
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
