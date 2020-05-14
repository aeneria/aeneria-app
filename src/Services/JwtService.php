<?php

namespace App\Services;

use Firebase\JWT\JWT;

/**
 * Data exporter services.
 */
class JwtService
{

    private $privateKey;
    private $publicKey;

    public function __construct(string $projectDir)
    {
        $this->privateKey = \sprintf(
            '%s/private/id_rsa',
            $projectDir
        );
        $this->publicKey = \sprintf(
            '%s/private/id_rsa.pub',
            $projectDir
        );
    }

    public function generateRsaKey(): void
    {
        $res = \openssl_pkey_new([
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        \openssl_pkey_export($res, $privKey);
        \file_put_contents($this->privateKey, $privKey);

        $pubKey = \openssl_pkey_get_details($res);
        \file_put_contents( $this->publicKey, $pubKey["key"]);
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