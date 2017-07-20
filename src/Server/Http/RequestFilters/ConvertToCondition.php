<?php

namespace Mcustiel\Phiremock\Server\Http\RequestFilters;

use Mcustiel\Phiremock\Domain\Condition;
use Mcustiel\Phiremock\Server\Config\Matchers;
use Mcustiel\SimpleRequest\Exception\FilterErrorException;
use Mcustiel\SimpleRequest\Interfaces\FilterInterface;

class ConvertToCondition implements FilterInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\SimpleRequest\Interfaces\FilterInterface::filter()
     */
    public function filter($value)
    {
        if ($value === null) {
            return;
        }
        $this->checkValueIsValidOrThrowException($value);
        $matcher = key($value);
        $this->validateMatcherOrThrowException($matcher);
        $this->validateValueOrThrowException($value[$matcher]);

        return new Condition($matcher, $value[$matcher]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Mcustiel\SimpleRequest\Interfaces\Specificable::setSpecification()
     * @SuppressWarnings("unused")
     */
    public function setSpecification($specification = null)
    {
    }

    /**
     * @param mixed $value
     *
     * @throws FilterErrorException
     */
    private function validateValueOrThrowException($value)
    {
        if ($value === null) {
            throw new FilterErrorException('Condition value can not be null');
        }
    }

    /**
     * @param string $matcher
     *
     * @throws FilterErrorException
     */
    private function validateMatcherOrThrowException($matcher)
    {
        if (!$this->isValidCondition($matcher)) {
            throw new FilterErrorException('Invalid condition matcher specified: ' . $matcher);
        }
    }

    /**
     * @param string $matcherName
     *
     * @return bool
     */
    private function isValidCondition($matcherName)
    {
        return $matcherName === Matchers::EQUAL_TO
            || $matcherName === Matchers::MATCHES
            || $matcherName === Matchers::SAME_STRING
            || $matcherName === Matchers::CONTAINS;
    }

    /**
     * @param mixed $value
     *
     * @throws FilterErrorException
     */
    private function checkValueIsValidOrThrowException($value)
    {
        if (!is_array($value) || count($value) !== 1) {
            throw new FilterErrorException(
                'Condition parsing failed for "'
                . var_export($value, true)
                . '", it should be something like: "isEqualTo" : "a value"'
            );
        }
    }
}
