<?php

/**
 * This file is part of phiremock-codeception-extension.
 *
 * phiremock-codeception-extension is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phiremock-codeception-extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phiremock-codeception-extension.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mcustiel\Phiremock\Server\Utils\Config;

use Exception;
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
    public const IP = 'ip';
    public const PORT = 'port';
    public const DEBUG = 'debug';
    public const EXPECTATIONS_DIR = 'expectations-dir';
    public const FACTORY_CLASS = 'factory-class';
    public const CERTIFICATE = 'certificate';
    public const CERTIFICATE_KEY = 'certificate-key';
    public const CERT_PASSPHRASE = 'cert-passphrase';

    public const CONFIG_OPTIONS = [
        self::IP,
        self::PORT,
        self::DEBUG,
        self::EXPECTATIONS_DIR,
        self::FACTORY_CLASS,
        self::CERTIFICATE,
        self::CERTIFICATE_KEY,
        self::CERT_PASSPHRASE,
    ];

    /** @var array */
    private $configurationArray;

    public function __construct(array $configurationArray)
    {
        $this->configurationArray = $configurationArray;
    }

    public function getInterfaceIp(): HostInterface
    {
        return new HostInterface($this->configurationArray[self::IP]);
    }

    public function getPort(): Port
    {
        return new Port((int) $this->configurationArray[self::PORT]);
    }

    public function isDebugMode(): bool
    {
        return $this->configurationArray[self::DEBUG];
    }

    public function getExpectationsPath(): ExpectationsDirectory
    {
        return new ExpectationsDirectory($this->configurationArray[self::EXPECTATIONS_DIR]);
    }

    public function getFactoryClassName(): PhpFactoryFqcn
    {
        return new PhpFactoryFqcn($this->configurationArray[self::FACTORY_CLASS]);
    }

    public function isSecure(): bool
    {
        return isset($this->configurationArray[self::CERTIFICATE])
            && isset($this->configurationArray[self::CERTIFICATE_KEY]);
    }

    /** @throws Exception */
    public function getSecureOptions(): ?SecureOptions
    {
        if (!$this->isSecure()) {
            return null;
        }

        return new SecureOptions(
            new CertificatePath($this->configurationArray[self::CERTIFICATE]),
            new CertificateKeyPath($this->configurationArray[self::CERTIFICATE_KEY]),
            isset($this->configurationArray[self::CERT_PASSPHRASE])
                ? new Passphrase($this->configurationArray[self::CERT_PASSPHRASE])
                : null
        );
    }
}
