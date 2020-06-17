<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Data exporter services.
 */
class JwtService
{
    private $privateDir;
    private $privateKey;
    private $publicKey;

    public function __construct(string $projectDir)
    {
        $this->privateDir = \sprintf(
            '%s/private',
            $projectDir
        );
        $this->privateKey = \sprintf(
            '%s/id_rsa',
            $this->privateDir
        );
        $this->publicKey = \sprintf(
            '%s/id_rsa.pub',
            $this->privateDir
        );
    }

    public function generateRsaKey(): void
    {
        // Ensure private direrctory exists
        if (!\is_dir($this->privateDir)) {
            \mkdir($this->privateDir);
        }

        $res = \openssl_pkey_new([
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => \OPENSSL_KEYTYPE_RSA,
        ]);

        \openssl_pkey_export($res, $privKey);
        if (false === \file_put_contents($this->privateKey, $privKey)) {
            throw new IOException(\sprintf(
                "Error while writting %s",
                $this->privateKey
            ));
        }

        $pubKey = \openssl_pkey_get_details($res);
        if (false === \file_put_contents($this->publicKey, $pubKey["key"])) {
            throw new IOException(\sprintf(
                "Error while writting %s",
                $this->publicKey
            ));
        }
    }

    public function encode($payload): string
    {
        return JWT::encode($payload, \file_get_contents($this->privateKey));
    }

    public function decode($jwt): \stdClass
    {
        return JWT::decode($jwt, \file_get_contents($this->privateKey), ['HS256']);
    }
}
