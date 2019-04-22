<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

class ExpectationsDirectory
{
    /** @var string */
    private $expectationsDir;

    /** @param string $expectationsDir */
    public function __construct($expectationsDir)
    {
        $this->ensureIsString($expectationsDir);
        $this->expectationsDir = $expectationsDir;
    }

    /** @return bool */
    public function exists()
    {
        return file_exists($this->expectationsDir);
    }

    /** @return bool */
    public function isDirectory()
    {
        return is_dir($this->expectationsDir);
    }

    public function create()
    {
        mkdir($this->expectationsDir, 0755, true);
    }

    /** @return string */
    public function asString()
    {
        return $this->expectationsDir;
    }

    /**
     * @param string $expectationsDir
     *
     * @throws \InvalidArgumentException
     */
    private function ensureIsString($expectationsDir)
    {
        if (!\is_string($expectationsDir)) {
            throw new \InvalidArgumentException(
                sprintf('Expected string argument. Got %s', \gettype($expectationsDir))
            );
        }
    }
}
