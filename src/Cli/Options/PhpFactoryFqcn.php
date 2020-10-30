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

use InvalidArgumentException;
use Mcustiel\Phiremock\Server\Factory\Factory;
use Mcustiel\Phiremock\Server\Utils\Config\Config;

class PhpFactoryFqcn
{
    private const CLASSNAME_REGEX = '~^(?:\\\\[a-z0-9_]+|[a-z0-9_]+)(?:\\\\[a-z0-9_]+)*$~i';

    /** @var string */
    private $className;

    public function __construct(string $className)
    {
        $this->ensureIsClassName($className);
        $this->ensureExtendsFactory($className);
        $this->className = $className;
    }

    public function asString(): string
    {
        return $this->className;
    }

    public function asInstance(Config $config): object
    {
        /** @var class-string<Factory> $className */
        $className = $this->className;

        return $className::createDefault($config);
    }

    private function ensureExtendsFactory(string $className): void
    {
        if (!is_a($className, Factory::class, true)) {
            throw new InvalidArgumentException(sprintf('Class %s does not extend %s', $className, Factory::class));
        }
    }

    private function ensureIsClassName(string $className): void
    {
        if (preg_match(self::CLASSNAME_REGEX, $className) !== 1) {
            throw new InvalidArgumentException('Invalid class name: ' . $className);
        }
    }
}
