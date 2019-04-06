<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

class Contains implements MatcherInterface
{
    public function match($value, $argument = null)
    {
        return strpos($value, $argument) !== false;
    }
}
