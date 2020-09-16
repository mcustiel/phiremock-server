<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

use Mcustiel\Phiremock\Server\Factory\Factory;
use Mcustiel\Phiremock\Server\Utils\Config\Config;

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

    public function asInstance(Config $config): object
    {
        $className = $this->className;

        return $className::createDefault($config);
    }

    private function ensureExtendsFactory(string $className): void
    {
        if (!is_a($className, Factory::class, true)) {
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
