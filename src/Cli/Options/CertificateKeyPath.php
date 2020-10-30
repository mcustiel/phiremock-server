<?php

/**
 * This file is part of phiremock-codeception-extension.
 *
 * phiremock-codeception-extension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phiremock-codeception-extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phiremock-codeception-extension.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mcustiel\Phiremock\Server\Cli\Options;

use Exception;

class CertificateKeyPath
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->ensureCanReadFile($path);
        $this->path = $path;
    }

    public function asString(): string
    {
        return $this->path;
    }

    private function ensureCanReadFile(string $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception(sprintf('File %s does not exist or is not readable', $path));
        }
    }
}
