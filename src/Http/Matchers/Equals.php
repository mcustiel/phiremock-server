<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

class Equals implements MatcherInterface
{
    public function match($value, $argument = null)
    {
        return $value === $argument;
    }
}
