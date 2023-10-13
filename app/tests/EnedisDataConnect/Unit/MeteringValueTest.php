<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Tests\Unit;

use App\EnedisDataConnect\Model\MeteringValue;
use PHPUnit\Framework\TestCase;

final class MeteringValueTest extends TestCase
{
    public function testHydratation()
    {
        $data = <<<JSON
        {
          "value": "540",
          "date": "2019-05-06",
          "interval_length": "P1D"
        }
        JSON;

        $meteringValue = MeteringValue::fromStdClass(\json_decode($data));

        self::assertInstanceOf(MeteringValue::class, $meteringValue);
        self::assertSame("2019-05-06", $meteringValue->getDate()->format('Y-m-d'));
        self::assertSame(540.0, $meteringValue->getValue());
        self::assertEquals(new \DateInterval('P1D'), $meteringValue->getIntervalLength());
    }
}
