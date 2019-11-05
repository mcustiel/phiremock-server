<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

class ExpectationsDirectory
{
    /** @var string */
    private $expectationsDir;

    /** @param string $expectationsDir */
    public function __construct(string $expectationsDir)
    {
        $this->expectationsDir = $expectationsDir;
    }

    public function exists(): bool
    {
        return file_exists($this->expectationsDir);
    }

    public function isDirectory(): bool
    {
        return is_dir($this->expectationsDir);
    }

    public function create(): void
    {
        mkdir($this->expectationsDir, 0755, true);
    }

    public function asString(): string
    {
        return $this->expectationsDir;
    }
}
