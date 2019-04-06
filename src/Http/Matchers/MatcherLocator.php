<?php

namespace Mcustiel\Phiremock\Server\Http\Matchers;

use Mcustiel\Phiremock\Domain\Conditions\MatchersEnum;

class MatcherLocator
{
    const MATCHER_FACTORY_METHOD_MAP = [
        MatchersEnum::CONTAINS    => 'createContains',
        MatchersEnum::EQUAL_TO    => 'createEquals',
        MatchersEnum::MATCHES     => 'createRegExp',
        MatchersEnum::SAME_JSON   => 'createJsonObjectContains',
        MatchersEnum::SAME_STRING => 'createCaseInsensitiveEquals',
    ];

    /** @var MatcherFactory */
    private $factory;

    public function __construct(MatcherFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $matcherIdentifier
     *
     * @throws \InvalidArgumentException
     *
     * @return MatcherInterface
     */
    public function locate($matcherIdentifier)
    {
        if (MatchersEnum::isValidMatcher($matcherIdentifier)) {
            return $this->factory->{self::MATCHER_FACTORY_METHOD_MAP[$matcherIdentifier]}();
        }
        throw new \InvalidArgumentException(
            sprintf(
                'Trying to match using %s. Which is not a valid matcher.',
                var_export($matcherIdentifier, true)
            )
        );
    }
}
