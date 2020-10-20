<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

class CertificateKeyPath
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->ensureCanReadFile($path);
        $this->path = $path;
    }

    public function asString(): string
    {
        return $this->path;
    }

    private function ensureCanReadFile(string $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new \Exception(sprintf('File %s does not exist or is not readable', $path));
        }
    }
}
