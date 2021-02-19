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

namespace Mcustiel\Phiremock\Server\Utils\DataStructures;

use ArrayIterator;
use BadMethodCallException;
use InvalidArgumentException;

class StringObjectArrayMap implements Map
{
    private $mapData;

    public function __construct()
    {
        $this->clean();
    }

    public function getIterator()
    {
        return new ArrayIterator($this->mapData);
    }

    public function set($key, $value)
    {
        if (!\is_string($key)) {
            throw new InvalidArgumentException('Expected key to be string. Got: ' . \gettype($key));
        }

        if (!\is_object($value)) {
            throw new InvalidArgumentException('Expected value to be object. Got: ' . \gettype($key));
        }
        $this->mapData[$key] = $value;
    }

    public function get($key)
    {
        if (!$this->has($key)) {
            throw new BadMethodCallException('Calling get for an absent key: ' . $key);
        }

        return $this->mapData[$key];
    }

    public function has($key)
    {
        if (!\is_string($key)) {
            throw new InvalidArgumentException('Expected key to be string. Got: ' . \gettype($key));
        }

        return isset($this->mapData[$key]);
    }

    public function clean()
    {
        $this->mapData = [];
    }

    public function delete($key)
    {
        if (!$this->has($key)) {
            throw new BadMethodCallException('Calling delete for an absent key: ' . $key);
        }
        unset($this->mapData[$key]);
    }
}
