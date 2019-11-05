<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

class CaseInsensitiveEquals implements MatcherInterface
{
    public function match($value, $argument = null): bool
    {
        return strtolower($value) === strtolower($argument);
    }
}
