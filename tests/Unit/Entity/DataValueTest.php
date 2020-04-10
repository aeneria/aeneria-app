<?php

namespace App\Tests\Unit\Entity;

use App\Entity\DataValue;
use App\Tests\AppTestCase;

final class DataValueTest extends AppTestCase
{
    public function testDataValueInstance()
    {
        $feedData = $this->createFeedData();
        $dataValue = $this->createDataValue([
            'id' => $dataValueId = \rand(),
            'feedData' => $feedData,
            'frequency' => DataValue::FREQUENCY['HOUR'],
            'value' => 42,
            'date' => $date = new \DateTimeImmutable('2 days ago'),
        ]);

        self::assertSame($dataValue->getId(), $dataValueId);
        self::assertSame($dataValue->getFeedData(), $feedData);
        self::assertSame($dataValue->getFrequency(), DataValue::FREQUENCY['HOUR']);
        self::assertSame($dataValue->getDate(), $date);
        self::assertSame($dataValue->getHour(), (int) $date->format('H'));
        self::assertSame($dataValue->getWeekDay(), (int) (0 == $date->format('w') ? 6 : $date->format('w') - 1));
        self::assertSame($dataValue->getWeek(), (int) $date->format('W'));
        self::assertSame($dataValue->getMonth(), (int) $date->format('m'));
        self::assertSame($dataValue->getYear(), (int) $date->format('Y'));
    }

    public function testAdaptToFrequency()
    {
        $date = new \DateTimeImmutable("2020-02-06 21:22:30");

        self::assertEquals(
            DataValue::adaptToFrequency($date, DataValue::FREQUENCY['HOUR']),
            new \DateTimeImmutable("2020-02-06 21:00:00")
        );
        self::assertEquals(
            DataValue::adaptToFrequency($date, DataValue::FREQUENCY['DAY']),
            new \DateTimeImmutable("2020-02-06 00:00:00")
        );
        self::assertEquals(
            DataValue::adaptToFrequency($date, DataValue::FREQUENCY['WEEK']),
            new \DateTimeImmutable("2020-02-03 00:00:00")
        );
        self::assertEquals(
            DataValue::adaptToFrequency($date, DataValue::FREQUENCY['MONTH']),
            new \DateTimeImmutable("2020-02-01 00:00:00")
        );
        self::assertEquals(
            DataValue::adaptToFrequency($date, DataValue::FREQUENCY['YEAR']),
            new \DateTimeImmutable("2020-01-01 00:00:00")
        );
    }

    public function testAdaptedBoundariesForFrequency()
    {
        $date = new \DateTimeImmutable("2020-02-06 21:22:30");

        $this->expectExceptionMessage("Can't adapt boundaries for this frequency !");
        $adaptedData = DataValue::getAdaptedBoundariesForFrequency($date, DataValue::FREQUENCY['HOUR']);

        $adaptedData = DataValue::getAdaptedBoundariesForFrequency($date, DataValue::FREQUENCY['DAY']);
        self::assertEquals($adaptedData['from'], new \DateTimeImmutable("2020-02-06 00:00:00"));
        self::assertEquals($adaptedData['to'], new \DateTimeImmutable("2020-02-07 00:00:00"));
        self::assertEquals($adaptedData['previousFrequency'], DataValue::FREQUENCY['HOUR']);

        $adaptedData = DataValue::getAdaptedBoundariesForFrequency($date, DataValue::FREQUENCY['WEEK']);
        self::assertEquals($adaptedData['from'], new \DateTimeImmutable("2020-02-03 00:00:00"));
        self::assertEquals($adaptedData['to'], new \DateTimeImmutable("2020-02-10 00:00:00"));
        self::assertEquals($adaptedData['previousFrequency'], DataValue::FREQUENCY['DAY']);

        $adaptedData = DataValue::getAdaptedBoundariesForFrequency($date, DataValue::FREQUENCY['MONTH']);
        self::assertEquals($adaptedData['from'], new \DateTimeImmutable("2020-02-01 00:00:00"));
        self::assertEquals($adaptedData['to'], new \DateTimeImmutable("2020-02-29 00:00:00"));
        self::assertEquals($adaptedData['previousFrequency'], DataValue::FREQUENCY['DAY']);

        $adaptedData = DataValue::getAdaptedBoundariesForFrequency($date, DataValue::FREQUENCY['YEAR']);
        self::assertEquals($adaptedData['from'], new \DateTimeImmutable("2020-01-01 00:00:00"));
        self::assertEquals($adaptedData['to'], new \DateTimeImmutable("2020-12-31 00:00:00"));
        self::assertEquals($adaptedData['previousFrequency'], DataValue::FREQUENCY['MONTH']);
    }
}
