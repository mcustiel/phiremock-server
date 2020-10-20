<?php

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
