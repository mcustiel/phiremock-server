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
use Mcustiel\Phiremock\Common\Utils\ArrayToScenarioStateInfoConverter;
use Mcustiel\Phiremock\Domain\ScenarioStateInfo;
use Mcustiel\Phiremock\Server\Model\ScenarioStorage;
use Mcustiel\PowerRoute\Actions\ActionInterface;
use Mcustiel\PowerRoute\Common\TransactionData;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class SetScenarioStateAction implements ActionInterface
{
    /**
     * @var \Mcustiel\Phiremock\Server\Model\ScenarioStorage
     */
    private $storage;

    /** @var ArrayToScenarioStateInfoConverter */
    private $converter;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \Mcustiel\Phiremock\Common\Utils\ArrayToExpectationConverter $requestBuilder
     * @param \Mcustiel\Phiremock\Server\Model\ScenarioStorage             $storage
     * @param \Psr\Log\LoggerInterface                                     $logger
     */
    public function __construct(
        ArrayToScenarioStateInfoConverter $requestBuilder,
        ScenarioStorage $storage,
        LoggerInterface $logger
    ) {
        $this->converter = $requestBuilder;
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\PowerRoute\Actions\ActionInterface::execute()
     */
    public function execute(TransactionData $transactionData, $argument = null)
    {
        $transactionData->setResponse(
            $this->processAndGetResponse(
                $transactionData,
                function (TransactionData $transaction, ScenarioStateInfo $state) {
                    $this->storage->setScenarioState($state->getScenarioName(), $state->getScenarioState());
                    $this->logger->debug(
                        'Scenario ' . $state->getScenarioName() . ' state is set to ' . $state->getScenarioState()
                    );

                    return $transaction->getResponse()
                        ->withStatus(200)
                        ->withHeader('Content-Type', 'application/json')
                        ->withBody(new StringStream(json_encode($state)));
                }
            )
        );
    }

    protected function parseRequestObject(ServerRequestInterface $request)
    {
        /** @var \Mcustiel\Phiremock\Domain\ScenarioState $object */
        $object = $this->converter->convert(
            $this->parseJsonBody($request)
        );
        $this->logger->debug('Parsed scenario state: ' . var_export($object, true));

        return $object;
    }
}
