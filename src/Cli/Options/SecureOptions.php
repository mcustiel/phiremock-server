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

namespace Mcustiel\Phiremock\Server\Cli\Options;

class SecureOptions
{
    /** @var CertificatePath */
    private $certificate;
    /** @var CertificateKeyPath */
    private $certificateKey;
    /** @var Passphrase */
    private $passphrase;

    public function __construct(CertificatePath $cert, CertificateKeyPath $certKey, ?Passphrase $pass)
    {
        $this->certificate = $cert;
        $this->passphrase = $pass;
        $this->certificateKey = $certKey;
    }

    public function getCertificate(): CertificatePath
    {
        return $this->certificate;
    }

    public function getCertificateKey(): CertificateKeyPath
    {
        return $this->certificateKey;
    }

    public function hasPassphrase(): bool
    {
        return $this->passphrase !== null;
    }

    public function getPassphrase(): Passphrase
    {
        return $this->passphrase;
    }
}
