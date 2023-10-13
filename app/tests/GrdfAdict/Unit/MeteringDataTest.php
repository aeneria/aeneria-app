<?php

declare(strict_types=1);

namespace App\GrdfAdict\Tests\Unit;

use App\GrdfAdict\Model\MeteringData;
use PHPUnit\Framework\TestCase;

final class MeteringDataTest extends TestCase
{
    public function testHydratation()
    {
        $data = <<<JSON
        {
          "consommation": {
            "journee_gaziere": "2019-05-06",
            "energie": "540"
          }
        }
        JSON;

        $meteringData = MeteringData::fromJson($data);

        self::assertInstanceOf(MeteringData::class, $meteringData);
        self::assertSame("2019-05-06", $meteringData->date->format('Y-m-d'));
        self::assertSame('540', $meteringData->value);
    }
}
