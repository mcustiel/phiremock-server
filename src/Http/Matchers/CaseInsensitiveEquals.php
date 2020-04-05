<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

class CaseInsensitiveEquals implements MatcherInterface
{
    public function match($value, $argument = null): bool
    {
        echo sprintf('Checking if %s is same string as %s', $argument, $value);

        return strtolower($value) === strtolower($argument);
    }
}
