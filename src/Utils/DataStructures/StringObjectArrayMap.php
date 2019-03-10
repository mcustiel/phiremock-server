<?php

namespace Mcustiel\Phiremock\Server\Utils\DataStructures;

class StringObjectArrayMap implements Map
{
    private $mapData;

    public function __construct()
    {
        $this->clean();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->mapData);
    }

    public function set($key, $value)
    {
        if (!\is_string($key)) {
            throw new \InvalidArgumentException('Expected key to be string. Got: ' . \gettype($key));
        }

        if (!\is_object($value)) {
            throw new \InvalidArgumentException('Expected value to be object. Got: ' . \gettype($key));
        }
        $this->mapData[$key] = $value;
    }

    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \BadMethodCallException('Calling get for an absent key: ' . $key);
        }

        return $this->mapData[$key];
    }

    public function has($key)
    {
        if (!\is_string($key)) {
            throw new \InvalidArgumentException('Expected key to be string. Got: ' . \gettype($key));
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
            throw new \BadMethodCallException('Calling delete for an absent key: ' . $key);
        }
        unset($this->mapData[$key]);
    }
}
