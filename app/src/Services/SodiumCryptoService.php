<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Filesystem\Exception\IOException;

class SodiumCryptoService
{
    private string $privateDir;
    private string $sodiumKeypairFilename;
    private ?string $keypair = null;
    private ?string $publicKey = null;

    public function __construct(string $projectDir)
    {
        $this->privateDir = \sprintf(
            '%s/private',
            $projectDir
        );
        $this->sodiumKeypairFilename = \sprintf(
            '%s/sodium-keypair',
            $this->privateDir
        );
    }

    public function keyExists(): bool
    {
        return \file_exists($this->sodiumKeypairFilename);
    }

    public function generateKeypair(): void
    {
        // Ensure private directory exists
        if (!\is_dir($this->privateDir)) {
            \mkdir($this->privateDir);
        }

        $this->keypair = \sodium_crypto_box_keypair();

        if (false === \file_put_contents($this->sodiumKeypairFilename, $this->keypair)) {
            throw new IOException(\sprintf(
                "Error while writting %s",
                $this->sodiumKeypairFilename
            ));
        }
    }

    public function getKeypair(): string
    {
        if ($this->keypair) {
            return $this->keypair;
        }

        return $this->keypair = \file_get_contents($this->sodiumKeypairFilename);
    }

    public function getPublicKey(): string
    {
        if ($this->publicKey) {
            return $this->publicKey;
        }

        return $this->publicKey = \sodium_crypto_box_publickey($this->getKeypair());
    }

    public function seal(string $payload): string
    {
        return \sodium_crypto_box_seal($payload, $this->getPublicKey());
    }

    public function open(string $encrypted): string|bool
    {
        return \sodium_crypto_box_seal_open($encrypted, $this->getKeypair());
    }
}
