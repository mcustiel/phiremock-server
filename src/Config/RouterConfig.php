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

namespace Mcustiel\Phiremock\Server\Config;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Mcustiel\Phiremock\Server\Actions\ActionsFactory;
use Psr\Http\Message\ServerRequestInterface;

class RouterConfig
{
    /** @var Dispatcher */
    private $dispatcher;

    public function __construct(ActionsFactory $factory)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(
            [$this, 'dispatcherConfig'],
            [
                'cacheFile'     => __DIR__ . '/route.cache',
                'cacheDisabled' => IS_DEBUG_MODE,
            ]
        );
    }

    public function dispatch(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        $routeInfo = $this->dispatcher->dispatch($method, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                // CALL THE REQUEST MANAGER

                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                // API ERROR
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                // ... call $handler with $vars
                break;
        }
    }

    private function dispatcherConfig(RouteCollector $r)
    {
        $r->addRoute('GET', '^/__phiremock/expectations/?$', 'get_all_users_handler');
        $r->addRoute('POST', '^/__phiremock/expectations/?$', 'get_all_users_handler');
        $r->addRoute('DELETE', '^/__phiremock/expectations/?$', 'get_all_users_handler');

        $r->addRoute('PUT', '^/__phiremock/scenarios/?$', 'get_all_users_handler');
        $r->addRoute('DELETE', '^/__phiremock/scenarios/?$', 'get_all_users_handler');

        $r->addRoute('POST', '^/__phiremock/executions/?$', 'get_all_users_handler');
        $r->addRoute('PUT', '^/__phiremock/executions/?$', 'get_all_users_handler');
        $r->addRoute('DELETE', '^/__phiremock/executions/?$', 'get_all_users_handler');

        $r->addRoute('POST', '^/__phiremock/reset/?$', 'get_all_users_handler');
    }
}
