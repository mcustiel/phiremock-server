<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

class RegExp implements MatcherInterface
{
    public function match($value, $argument = null): bool
    {
        echo sprintf('Checking if %s matches pattern %s', $value, $argument);

        return (bool) preg_match($argument, $value);
    }
}
