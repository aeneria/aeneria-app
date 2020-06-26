<?php

declare(strict_types=1);

namespace App\Tests\Functional\Services;

use App\Services\JwtService;
use App\Tests\AppTestCase;

final class JwtServiceTest extends AppTestCase
{
    public function testJwtService()
    {
        $jwtService = new JwtService($this->getResourceDir());

        $jwtService->generateRsaKey();

        self::assertFileExists($this->getResourceDir() . '/private/id_rsa');
        self::assertFileExists($this->getResourceDir() . '/private/id_rsa.pub');

        $encoded = $jwtService->encode($raw = 'tottototo_&"{');
        $decoded = $jwtService->decode($encoded);

        self::assertSame($raw, $decoded);
    }
}
