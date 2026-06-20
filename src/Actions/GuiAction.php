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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class GuiAction implements ActionInterface
{
    public function execute(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody(new StringStream($this->getHtml()));
    }

    private function getHtml(): string
    {
        $htmlPath = $this->getHtmlPath();
        if (!is_readable($htmlPath)) {
            throw new RuntimeException(sprintf('Unable to read GUI HTML resource from %s', $htmlPath));
        }

        $html = file_get_contents($htmlPath);
        if ($html === false) {
            throw new RuntimeException(sprintf('Unable to read GUI HTML resource from %s', $htmlPath));
        }

        return $html;
    }

    private function getHtmlPath(): string
    {
        return dirname(__DIR__, 2) . '/resources/gui.html';
    }
}
