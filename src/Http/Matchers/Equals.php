<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

class Equals implements MatcherInterface
{
    public function match($value, $argument = null): bool
    {
        echo sprintf('Checking if %s is equal to %s', $value, $argument);

        return $value === $argument;
    }
}
