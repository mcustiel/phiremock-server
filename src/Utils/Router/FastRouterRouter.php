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

namespace Mcustiel\Phiremock\Server\Utils\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Mcustiel\Phiremock\Server\Actions\ActionLocator;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class FastRouterRouter implements RouterInterface
{
    /** @var Dispatcher */
    private $dispatcher;
    /** @var ActionLocator */
    private $actionsLocator;

    public function __construct(ActionLocator $locator)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(
            $this->createDispatcherCallable(),
            [
                'cacheFile'     => __DIR__ . '/route.cache',
                'cacheDisabled' => IS_DEBUG_MODE,
            ]
        );
        $this->actionsLocator = $locator;
    }

    public function dispatch(ServerRequestInterface $request)
    {
        $uri = $request->getUri()->getPath();
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return $this->actionsLocator
                    ->locate(ActionLocator::MANAGE_REQUEST)
                    ->execute($request, new Response());
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new Response(
                    sprintf(
                        'Method not allowed. Allowed methods for %s: %s',
                        $uri,
                        implode(', ', $routeInfo[1])
                    ),
                    405
                );
                break;
            case Dispatcher::FOUND:
                return $this->actionsLocator
                    ->locate($routeInfo[1])
                    ->execute($request, new Response());
        }
    }

    private function createDispatcherCallable()
    {
        return function (RouteCollector $r) {
            $r->addRoute('GET', '/__phiremock/expectations', ActionLocator::LIST_EXPECTATIONS);
            $r->addRoute('POST', '/__phiremock/expectations', ActionLocator::ADD_EXPECTATION);
            $r->addRoute('DELETE', '/__phiremock/expectations', ActionLocator::CLEAR_EXPECTATIONS);

            $r->addRoute('PUT', '/__phiremock/scenarios', ActionLocator::SET_SCENARIO_STATE);
            $r->addRoute('DELETE', '/__phiremock/scenarios', ActionLocator::CLEAR_SCENARIOS);

            $r->addRoute('POST', '/__phiremock/executions', ActionLocator::COUNT_REQUESTS);
            $r->addRoute('PUT', '/__phiremock/executions', ActionLocator::LIST_REQUESTS);
            $r->addRoute('DELETE', '/__phiremock/executions', ActionLocator::RESET_REQUESTS_COUNT);

            $r->addRoute('POST', '/__phiremock/reset', ActionLocator::RELOAD_EXPECTATIONS);
        };
    }
}
