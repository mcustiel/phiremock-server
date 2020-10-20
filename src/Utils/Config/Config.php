<?php

namespace Mcustiel\Phiremock\Server\Utils\Config;

use Mcustiel\Phiremock\Server\Cli\Options\CertificateKeyPath;
use Mcustiel\Phiremock\Server\Cli\Options\CertificatePath;
use Mcustiel\Phiremock\Server\Cli\Options\ExpectationsDirectory;
use Mcustiel\Phiremock\Server\Cli\Options\HostInterface;
use Mcustiel\Phiremock\Server\Cli\Options\Passphrase;
use Mcustiel\Phiremock\Server\Cli\Options\PhpFactoryFqcn;
use Mcustiel\Phiremock\Server\Cli\Options\Port;
use Mcustiel\Phiremock\Server\Cli\Options\SecureOptions;

class Config
{
    /** @var array */
    private $configurationArray;

    public function __construct(array $configurationArray)
    {
        $this->configurationArray = $configurationArray;
    }

    public function getInterfaceIp(): HostInterface
    {
        return new HostInterface($this->configurationArray['ip']);
    }

    public function getPort(): Port
    {
        return new Port((int) $this->configurationArray['port']);
    }

    public function isDebugMode(): bool
    {
        return $this->configurationArray['debug'];
    }

    public function getExpectationsPath(): ExpectationsDirectory
    {
        return new ExpectationsDirectory($this->configurationArray['expectations-dir']);
    }

    public function getFactoryClassName(): PhpFactoryFqcn
    {
        return new PhpFactoryFqcn($this->configurationArray['factory-class']);
    }

    public function isSecure(): bool
    {
        return isset($this->configurationArray['certificate'])
            && isset($this->configurationArray['certificate-key']);
    }

    public function getSecureOptions(): ?SecureOptions
    {
        if (!$this->isSecure()) {
            return null;
        }

        return new SecureOptions(
            new CertificatePath($this->configurationArray['certificate']),
            new CertificateKeyPath($this->configurationArray['certificate-key']),
            isset($this->configurationArray['cert-passphrase'])
                ? new Passphrase($this->configurationArray['cert-passphrase'])
                : null
        );
    }
}
