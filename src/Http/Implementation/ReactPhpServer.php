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

use Mcustiel\Phiremock\Server\Cli\Options\HostInterface;
use Mcustiel\Phiremock\Server\Cli\Options\Port;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory as EventLoop;
use React\Http\Server;
use React\Socket\Server as ReactSocket;

class ReactPhpServer implements ServerInterface
{
    /** @var \Mcustiel\Phiremock\Server\Http\RequestHandlerInterface */
    private $requestHandler;

    /** @var \React\EventLoop\LoopInterface */
    private $loop;

    /** @var \React\Socket\Server */
    private $socket;

    /** @var \React\Http\Server */
    private $http;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(RequestHandlerInterface $requestHandler, LoggerInterface $logger)
    {
        $this->loop = EventLoop::create();
        $this->logger = $logger;
        $this->requestHandler = $requestHandler;
    }

    public function listen(HostInterface $host, Port $port): void
    {
        $this->http = new Server(
            function (ServerRequestInterface $request) {
                return $this->onRequest($request);
            }
        );

        $listenConfig = "{$host->asString()}:{$port->asInt()}";
        $this->logger->info("Phiremock http server listening on {$listenConfig}");
        $this->socket = new ReactSocket($listenConfig, $this->loop);
        $this->http->listen($this->socket);

        // Dispatch pending signals periodically
        if (\function_exists('pcntl_signal_dispatch')) {
            $this->loop->addPeriodicTimer(0.5, function () {
                pcntl_signal_dispatch();
            });
        }
        $this->loop->run();
    }

    public function shutdown(): void
    {
        $this->loop->stop();
    }

    private function onRequest(ServerRequestInterface $request): ResponseInterface
    {
        $start = microtime(true);
        $psrResponse = $this->requestHandler->dispatch(new ServerRequestWithCachedBody($request));
        $this->logger->debug('Processing took ' . number_format((microtime(true) - $start) * 1000, 3) . ' milliseconds');

        return $psrResponse;
    }
}
