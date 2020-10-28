<?php

namespace Mcustiel\Phiremock\Server\Utils\Config;

use InvalidArgumentException;

class Directory
{
    /** @var string */
    private $directory;

    public function __construct(string $directory)
    {
        $this->ensureIsDirectory($directory);
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);
    }

    public function asString(): string
    {
        return $this->directory;
    }

    public function getFullSubpathAsString(string $subPath): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $subPath;
    }

    private function ensureIsDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a directory or is not accessible.', $directory));
        }
    }
}
