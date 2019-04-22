<?php
namespace Mcustiel\Phiremock\Server\Cli\Options;

class Flag
{
    /** @var bool */
    private $value;

    /** @param bool $flagValue */
    public function __construct($flagValue)
    {
        $this->ensureIsBoolean($flagValue);
        $this->value = $flagValue;
    }

    /** @return boolean */
    public function isTrue()
    {
        return $this->value;
    }

    /**
     * @param bool $flagValue
     * @throws \InvalidArgumentException
     */
    private function ensureIsBoolean($flagValue)
    {
        if (!is_bool($flagValue)) {
            throw new \InvalidArgumentException(
                sprintf('Expected boolean value. Got %s', gettype($flagValue))
            );
        }
    }
}
