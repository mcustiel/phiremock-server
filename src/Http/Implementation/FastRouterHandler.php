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

namespace Mcustiel\Phiremock\Server\Http\Implementation;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Laminas\Diactoros\Response;
use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Server\Actions\ActionLocator;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use Mcustiel\Phiremock\Server\Utils\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use function FastRoute\simpleDispatcher;

class FastRouterHandler implements RequestHandlerInterface
{
    /** @var Dispatcher */
    private $dispatcher;
    /** @var ActionLocator */
    private $actionsLocator;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(ActionLocator $locator, Config $config, LoggerInterface $logger)
    {
        $this->dispatcher = simpleDispatcher(
            $this->createDispatcherCallable(),
            [
                'cacheFile'     => __DIR__ . '/route.cache',
                'cacheDisabled' => $config->isDebugMode(),
            ]
        );
        $this->actionsLocator = $locator;
        $this->logger = $logger;
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $uri);
        try {
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    return $this->actionsLocator
                        ->locate(ActionLocator::MANAGE_REQUEST)
                        ->execute($request, new Response());
                case Dispatcher::METHOD_NOT_ALLOWED:
                    return new Response(
                        new StringStream(sprintf(
                            'Method not allowed. Allowed methods for %s: %s',
                            $uri,
                            implode(', ', $routeInfo[1])
                        )),
                        405
                    );
                case Dispatcher::FOUND:
                    return $this->actionsLocator
                        ->locate($routeInfo[1])
                        ->execute($request, new Response());
            }
            return new Response(
                new StringStream(
                    json_encode(['result' => 'ERROR', 'details' => 'Unexpected error: Router returned unexpected info'])
                ),
                500,
                ['Content-Type' => 'application/json']
            );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $this->logger->debug((string) $e);

            return new Response(
                new StringStream(
                    json_encode(['result' => 'ERROR', 'details' => $e->getMessage()])
                ),
                500,
                ['Content-Type' => 'application/json']
            );
        }
    }

    private function createDispatcherCallable(): callable
    {
        return function (RouteCollector $r) {
            $r->addRoute('GET', '/__phiremock/static/{path:.*}', ActionLocator::STATIC_FILES_SERVER);

            $r->addRoute('GET', '/__phiremock/expectations', ActionLocator::LIST_EXPECTATIONS);
            $r->addRoute('POST', '/__phiremock/expectations', ActionLocator::ADD_EXPECTATION);
            $r->addRoute('DELETE', '/__phiremock/expectations', ActionLocator::CLEAR_EXPECTATIONS);

            $r->addRoute('PUT', '/__phiremock/scenarios', ActionLocator::SET_SCENARIO_STATE);
            $r->addRoute('DELETE', '/__phiremock/scenarios', ActionLocator::CLEAR_SCENARIOS);

            $r->addRoute('POST', '/__phiremock/executions', ActionLocator::COUNT_REQUESTS);
            $r->addRoute('PUT', '/__phiremock/executions', ActionLocator::LIST_REQUESTS);
            $r->addRoute('DELETE', '/__phiremock/executions', ActionLocator::RESET_REQUESTS_COUNT);

            $r->addRoute('POST', '/__phiremock/reset', ActionLocator::RESET);
        };
    }
}
