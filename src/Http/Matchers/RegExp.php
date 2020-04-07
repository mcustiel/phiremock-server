<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

class RegExp implements MatcherInterface
{
    public function match($value, $argument = null): bool
    {
        return (bool) preg_match($argument, $value);
    }
}
