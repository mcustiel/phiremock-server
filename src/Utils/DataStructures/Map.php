<?php

namespace Mcustiel\Phiremock\Server\Utils\DataStructures;

use IteratorAggregate;

interface Map extends IteratorAggregate
{
    public function set($key, $value);

    public function get($key);

    public function clean();

    public function has($key);

    public function delete($key);

    public function getIterator();
}
