<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Symfony\Component\Filesystem\Exception\IOException;

class JwtService
{
    private string $privateDir;
    private string $privateKeyFilename;
    private ?string $privateKey = null;
    private string $publicKeyFilename;
    private ?string $publicKey = null;

    public function __construct(string $projectDir)
    {
        $this->privateDir = \sprintf(
            '%s/private',
            $projectDir
        );
        $this->privateKeyFilename = \sprintf(
            '%s/id_rsa',
            $this->privateDir
        );
        $this->publicKeyFilename = \sprintf(
            '%s/id_rsa.pub',
            $this->privateDir
        );
    }

    public function keyExists(): bool
    {
        return \file_exists($this->privateKeyFilename) && \file_exists($this->publicKeyFilename);
    }

    public function generateRsaKey(): void
    {
        // Ensure private directory exists
        if (!\is_dir($this->privateDir)) {
            \mkdir($this->privateDir);
        }

        $res = \openssl_pkey_new([
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => \OPENSSL_KEYTYPE_RSA,
        ]);

        \openssl_pkey_export($res, $privKey);
        if (false === \file_put_contents($this->privateKeyFilename, $privKey)) {
            throw new IOException(\sprintf(
                "Error while writting %s",
                $this->privateKeyFilename
            ));
        }

        $pubKey = \openssl_pkey_get_details($res);
        if (false === \file_put_contents($this->publicKeyFilename, $pubKey["key"])) {
            throw new IOException(\sprintf(
                "Error while writting %s",
                $this->publicKeyFilename
            ));
        }
    }

    public function getPrivateKey(): string
    {
        if ($this->privateKey) {
            return $this->privateKey;
        }

        return $this->privateKey = \file_get_contents($this->privateKeyFilename);
    }

    public function getPublicKey(): string
    {
        if ($this->publicKey) {
            return $this->publicKey;
        }

        return $this->publicKey = \file_get_contents($this->publicKeyFilename);
    }

    public function encode($payload): string
    {
        return JWT::encode($payload, $this->getPrivateKey());
    }

    public function decode($jwt)
    {
        return JWT::decode($jwt, $this->getPrivateKey(), ['HS256']);
    }
}
