<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

class Contains implements MatcherInterface
{
    public function match($value, $argument = null): bool
    {
        echo sprintf('Checking if %s contains to %s', $argument, $value);

        return strpos($value, $argument) !== false;
    }
}
