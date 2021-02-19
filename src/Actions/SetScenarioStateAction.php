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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class SetScenarioStateAction implements ActionInterface
{
    /** @var ScenarioStorage */
    private $storage;

    /** @var ArrayToScenarioStateInfoConverter */
    private $converter;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ArrayToScenarioStateInfoConverter $requestBuilder,
        ScenarioStorage $storage,
        LoggerInterface $logger
    ) {
        $this->converter = $requestBuilder;
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /** @throws \Exception */
    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $state = $this->parseRequestObject($request);
        if ($state->getScenarioName() === null || $state->getScenarioState() === null) {
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(
                    new StringStream(
                        json_encode(['error' => 'Scenario name or state is not set'])
                    )
                );
        }

        $this->storage->setScenarioState($state);
        $this->logger->debug(
            sprintf(
                'Scenario %s state is set to %s',
                $state->getScenarioName()->asString(),
                $state->getScenarioState()->asString()
            )
        );

        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($request->getBody());
    }

    /** @throws \Exception */
    private function parseRequestObject(ServerRequestInterface $request): ScenarioStateInfo
    {
        $object = $this->converter->convert(
            $this->parseJsonBody($request)
        );
        $this->logger->debug('Parsed scenario state: ' . var_export($object, true));

        return $object;
    }

    /** @throws \Exception */
    private function parseJsonBody(ServerRequestInterface $request): array
    {
        $body = $request->getBody()->__toString();
        $this->logger->debug($body);
        if ($request->hasHeader('Content-Encoding') && 'base64' === implode(',', $request->getHeader('Content-Encoding'))) {
            $body = base64_decode($body, true);
        }

        $bodyJson = @json_decode($body, true);
        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(json_last_error_msg());
        }

        return $bodyJson;
    }
}
