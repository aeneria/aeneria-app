<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Tests\Unit;

use App\EnedisDataConnect\Model\MeteringData;
use PHPUnit\Framework\TestCase;

final class MeteringDataTest extends TestCase
{
    public function testHydratation()
    {
        $data = <<<JSON
        {
          "meter_reading": {
            "usage_point_id": "16401220101758",
            "start": "2019-05-06",
            "end": "2019-05-12",
            "quality": "BRUT",
            "reading_type": {
              "measurement_kind": "energy",
              "measuring_period": "P1D",
              "unit": "Wh",
              "aggregate": "sum"
            },
            "interval_reading": [
              {
                "value": "540",
                "date": "2019-05-06"
              }
            ]
          }
        }
        JSON;

        $meteringData = MeteringData::fromJson($data, MeteringData::TYPE_DAILY_CONSUMPTION);

        self::assertInstanceOf(MeteringData::class, $meteringData);
        self::assertSame("16401220101758", $meteringData->getUsagePointId());
        self::assertSame("2019-05-06", $meteringData->getStart()->format('Y-m-d'));
        self::assertSame("2019-05-12", $meteringData->getEnd()->format('Y-m-d'));
        self::assertSame("Wh", $meteringData->getUnit());
        self::assertSame(MeteringData::TYPE_DAILY_CONSUMPTION, $meteringData->getDataType());
        self::assertCount(1, $meteringData->getValues());
    }
}
