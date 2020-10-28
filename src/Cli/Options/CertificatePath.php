<?php

namespace Mcustiel\Phiremock\Server\Cli\Options;

use Exception;

class CertificatePath
{
    /** @var string */
    private $path;

    /** @throws Exception */
    public function __construct(string $path)
    {
        $this->ensureCanReadFile($path);
        $this->path = $path;
    }

    public function asString(): string
    {
        return $this->path;
    }

    /** @throws Exception */
    private function ensureCanReadFile(string $path): void
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception(sprintf('File %s does not exist or is not readable', $path));
        }
    }
}
