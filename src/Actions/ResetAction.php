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

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResetAction implements ActionInterface
{
    /** @var ClearScenariosAction */
    private $scenariosCleaner;
    /** @var ResetRequestsCountAction */
    private $requestCounterCleaner;
    /** @var ReloadPreconfiguredExpectationsAction */
    private $expectationsReloader;
    /** @var Logger */
    private $logger;

    public function __construct(
        ClearScenariosAction $scenariosCleaner,
        ResetRequestsCountAction $requestCounterCleaner,
        ReloadPreconfiguredExpectationsAction $expectationsReloader,
        Logger $logger
    ) {
        $this->scenariosCleaner = $scenariosCleaner;
        $this->requestCounterCleaner = $requestCounterCleaner;
        $this->expectationsReloader = $expectationsReloader;
        $this->logger = $logger;
    }

    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->logger->debug('Executing reset');
        $response = $this->scenariosCleaner->execute($request, $response);
        $response = $this->requestCounterCleaner->execute($request, $response);

        return $this->expectationsReloader->execute($request, $response);
    }
}
