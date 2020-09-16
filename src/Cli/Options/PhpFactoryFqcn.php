<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

use Mcustiel\Phiremock\Server\Factory\Factory;

class PhpFactoryFqcn
{
    private const CLASSNAME_REGEX = '~^(?:\\\\[a-z0-9_]+|[a-z0-9_]+)(?:\\\\[a-z0-9_]+)*$~i';

    /** @var string */
    private $className;

    public function __construct(string $className)
    {
        $this->ensureIsClassName($className);
        $this->ensureExtendsFactory($className);
        $this->className = $className;
    }

    public function asString(): string
    {
        return $this->className;
    }

    public function asInstance(): object
    {
        $className = $this->className;

        return $className::createDefault();
    }

    private function ensureExtendsFactory(string $className): void
    {
        if ($className !== Factory::class && !is_a($className, Factory::class)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not extend %s', $className, Factory::class));
        }
    }

    private function ensureIsClassName(string $className): void
    {
        if (preg_match(self::CLASSNAME_REGEX, $className) !== 1) {
            throw new \InvalidArgumentException('Invalid class name: ' . $className);
        }
    }
}
