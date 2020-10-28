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

use Mcustiel\Phiremock\Common\StringStream;
use Mcustiel\Phiremock\Server\Cli\Options\HostInterface;
use Mcustiel\Phiremock\Server\Cli\Options\Port;
use Mcustiel\Phiremock\Server\Cli\Options\SecureOptions;
use Mcustiel\Phiremock\Server\Http\RequestHandlerInterface;
use Mcustiel\Phiremock\Server\Http\ServerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory as EventLoop;
use React\EventLoop\LoopInterface;
use React\Http\Server;
use React\Socket\Server as ReactSocket;

class ReactPhpServer implements ServerInterface
{
    /** @var RequestHandlerInterface */
    private $requestHandler;

    /** @var LoopInterface */
    private $loop;

    /** @var ReactSocket */
    private $socket;

    /** @var Server */
    private $http;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(RequestHandlerInterface $requestHandler, LoggerInterface $logger)
    {
        $this->loop = EventLoop::create();
        $this->logger = $logger;
        $this->requestHandler = $requestHandler;
    }

    public function listen(HostInterface $host, Port $port, ?SecureOptions $secureOptions): void
    {
        $this->http = new Server(
            $this->loop,
            function (ServerRequestInterface $request) {
                return $this->onRequest($request);
            }
        );

        $listenConfig = "{$host->asString()}:{$port->asInt()}";
        $this->initSocket($listenConfig, $secureOptions);
        $this->http->listen($this->socket);

        // Dispatch pending signals periodically
        if (function_exists('pcntl_signal_dispatch')) {
            $this->loop->addPeriodicTimer(0.5, function () {
                pcntl_signal_dispatch();
            });
        }
        $this->loop->run();
    }

    public function shutdown(): void
    {
        $this->http->removeAllListeners();
        $this->socket->close();
        $this->loop->stop();
    }

    private function onRequest(ServerRequestInterface $request): ResponseInterface
    {
        $start = microtime(true);

        // TODO: Remove this patch if ReactPHP is fixed
        if ($request->getParsedBody() !== null) {
            $request = $request->withBody(new StringStream(http_build_query($request->getParsedBody())));
        }

        $psrResponse = $this->requestHandler->dispatch(new ServerRequestWithCachedBody($request));
        $this->logger->debug('Processing took ' . number_format((microtime(true) - $start) * 1000, 3) . ' milliseconds');

        return $psrResponse;
    }

    private function initSocket(string $listenConfig, ?SecureOptions $secureOptions): void
    {
        $this->logger->info(
            sprintf(
                'Phiremock http server listening on %s over %s',
                $listenConfig,
                null === $secureOptions ? 'http' : 'https'
            )
        );
        $context = [];
        if ($secureOptions !== null) {
            $tlsContext = [];
            $listenConfig = sprintf('tls://%s', $listenConfig);
            $tlsContext['local_cert'] = $secureOptions->getCertificate()->asString();
            $tlsContext['local_pk'] = $secureOptions->getCertificateKey()->asString();
            if ($secureOptions->hasPassphrase()) {
                $tlsContext['passphrase'] = $secureOptions->getPassphrase()->asString();
            }
            $context['tls'] = $tlsContext;
        }
        $this->socket = new ReactSocket($listenConfig, $this->loop, $context);
    }
}
