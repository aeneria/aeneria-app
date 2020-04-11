<?php

namespace App\Tests\Unit\Repository;

use App\Controller\DataController;
use App\Entity\DataValue;
use App\Entity\FeedData;
use App\Tests\AppTestCase;

final class DataValueRepositoryTest extends AppTestCase
{
    public function testPersistAndFind()
    {
        $entityManager = $this->getEntityManager();
        $dataValueRepository = $this->getDataValueRepository();

        $dataValue = $this->createPersistedDataValue();

        $entityManager->flush();
        $entityManager->clear();

        $dataValueFromRepo = $dataValueRepository->find($dataValue->getId());

        self::assertSame($dataValue->getId(), $dataValueFromRepo->getId());
    }

    public function testUpdateOrCreateValue()
    {
        $entityManager = $this->getEntityManager();
        $dataValueRepository = $this->getDataValueRepository();

        $feedData = $this->createPersistedFeedData();

        $entityManager->flush();

        $dataValueRepository->updateOrCreateValue(
            $feedData,
            new \DateTimeImmutable('2020-02-05 00:00'),
            DataValue::FREQUENCY['HOUR'],
            42.0
        );

        $entityManager->flush();
        $entityManager->clear();

        $dataValue = $dataValueRepository->findOneBy([
            'feedData' => $feedData,
            'frequency' => DataValue::FREQUENCY['HOUR'],
            'date' => new \DateTimeImmutable('2020-02-05 00:00'),
        ]);

        self::assertSame(42.0, $dataValue->getValue());

        $dataValueRepository->updateOrCreateValue(
            $feedData,
            new \DateTimeImmutable('2020-02-05 00:00'),
            DataValue::FREQUENCY['HOUR'],
            72.0
        );

        $dataValueNew = $dataValueRepository->findOneBy([
            'feedData' => $feedData,
            'frequency' => DataValue::FREQUENCY['HOUR'],
            'date' => new \DateTimeImmutable('2020-02-05 00:00'),
        ]);

        self::assertEquals(72.0, $dataValueNew->getValue());
        self::assertSame($dataValue->getId(), $dataValueNew->getId());
    }

    public function testUpdateOrCreateAgregateValueByDay()
    {
        $entityManager = $this->getEntityManager();
        $dataValueRepository = $this->getDataValueRepository();

        $feed = $this->createPersistedFeed();
        $feedData = $this->createPersistedFeedData([
            'feed' => $feed,
            'dataType' => FeedData::FEED_DATA_CONSO_ELEC,
        ]);

        $entityManager->flush();

        $data = [
            '2020-04-03 23:00' => 42,
            '2020-04-04 00:00' => 42,
            '2020-04-04 01:00' => 42,
            '2020-04-04 02:00' => 42,
            '2020-04-04 03:00' => 42,
            '2020-04-04 04:00' => 42,
            '2020-04-05 00:00' => 42,
        ];

        foreach ($data as $date => $value) {
            $dataValueRepository->updateOrCreateValue(
                $feedData,
                new \DateTimeImmutable($date),
                DataValue::FREQUENCY['HOUR'],
                $value
            );
        }

        $entityManager->flush();
        $entityManager->clear();

        $dataValueRepository->updateOrCreateAgregateValue(
            new \DateTimeImmutable('2020-04-04 00:00'),
            $feed,
            DataValue::FREQUENCY['DAY']
        );
        $entityManager->flush();
        $dataValue = $dataValueRepository->findOneBy([
            'feedData' => $feedData,
            'frequency' => DataValue::FREQUENCY['DAY'],
            'date' => new \DateTimeImmutable('2020-04-04 00:00'),
        ]);
        self::assertEquals(210.0, $dataValue->getValue());
    }

    public function testUpdateOrCreateAgregateValueByWeek()
    {
        $entityManager = $this->getEntityManager();
        $dataValueRepository = $this->getDataValueRepository();

        $feed = $this->createPersistedFeed();
        $feedData = $this->createPersistedFeedData([
            'feed' => $feed,
            'dataType' => FeedData::FEED_DATA_CONSO_ELEC,
        ]);

        $entityManager->flush();

        $data = [
            '2020-03-29 00:00' => 42,
            '2020-04-04 00:00' => 42,
            '2020-04-05 00:00' => 42,
            '2020-04-06 00:00' => 42,
        ];

        foreach ($data as $date => $value) {
            $dataValueRepository->updateOrCreateValue(
                $feedData,
                new \DateTimeImmutable($date),
                DataValue::FREQUENCY['DAY'],
                $value
            );
        }

        $entityManager->flush();
        $entityManager->clear();

        $dataValueRepository->updateOrCreateAgregateValue(
            new \DateTimeImmutable('2020-04-04 00:00'),
            $feed,
            DataValue::FREQUENCY['WEEK']
        );
        $entityManager->flush();
        $dataValue = $dataValueRepository->findOneBy([
            'feedData' => $feedData,
            'frequency' => DataValue::FREQUENCY['WEEK'],
            'date' => new \DateTimeImmutable('2020-03-30 00:00'),
        ]);
        self::assertEquals(84.0, $dataValue->getValue());
    }

    public function testUpdateOrCreateAgregateValueByMonth()
    {
        $entityManager = $this->getEntityManager();
        $dataValueRepository = $this->getDataValueRepository();

        $feed = $this->createPersistedFeed();
        $feedData = $this->createPersistedFeedData([
            'feed' => $feed,
            'dataType' => FeedData::FEED_DATA_CONSO_ELEC,
        ]);

        $entityManager->flush();

        $data = [
            '2020-03-30 00:00' => 42,
            '2020-04-04 00:00' => 42,
            '2020-04-05 00:00' => 42,
            '2020-05-01 00:00' => 42,
        ];

        foreach ($data as $date => $value) {
            $dataValueRepository->updateOrCreateValue(
                $feedData,
                new \DateTimeImmutable($date),
                DataValue::FREQUENCY['DAY'],
                $value
            );
        }

        $entityManager->flush();
        $entityManager->clear();

        $dataValueRepository->updateOrCreateAgregateValue(
            new \DateTimeImmutable('2020-04-04 00:00'),
            $feed,
            DataValue::FREQUENCY['MONTH']
        );
        $entityManager->flush();
        $dataValue = $dataValueRepository->findOneBy([
            'feedData' => $feedData,
            'frequency' => DataValue::FREQUENCY['MONTH'],
            'date' => new \DateTimeImmutable('2020-04-01 00:00'),
        ]);
        self::assertEquals(84.0, $dataValue->getValue());
    }

    public function testUpdateOrCreateAgregateValueByYear()
    {
        $entityManager = $this->getEntityManager();
        $dataValueRepository = $this->getDataValueRepository();

        $feed = $this->createPersistedFeed();
        $feedData = $this->createPersistedFeedData([
            'feed' => $feed,
            'dataType' => FeedData::FEED_DATA_CONSO_ELEC,
        ]);

        $entityManager->flush();

        $data = [
            '2019-12-00 00:00' => 42,
            '2020-01-01 00:00' => 42,
            '2020-02-01 00:00' => 42,
            '2021-01-01 00:00' => 42,
        ];

        foreach ($data as $date => $value) {
            $dataValueRepository->updateOrCreateValue(
                $feedData,
                new \DateTimeImmutable($date),
                DataValue::FREQUENCY['MONTH'],
                $value
            );
        }

        $entityManager->flush();
        $entityManager->clear();

        $dataValueRepository->updateOrCreateAgregateValue(
            new \DateTimeImmutable('2020-04-04 00:00'),
            $feed,
            DataValue::FREQUENCY['YEAR']
        );
        $entityManager->flush();
        $dataValue = $dataValueRepository->findOneBy([
            'feedData' => $feedData,
            'frequency' => DataValue::FREQUENCY['YEAR'],
            'date' => new \DateTimeImmutable('2020-01-01 00:00'),
        ]);
        self::assertEquals(84.0, $dataValue->getValue());
    }

    public function provideDataValue()
    {
        return [
            [[
                'values' => [
                    '2020-04-03 00:00' => 40.0,
                    '2020-04-04 00:00' => 41.0,
                    '2020-04-05 00:00' => 42.0,
                    '2020-04-06 00:00' => 43.0,
                    '2020-04-07 00:00' => 44.0,
                    '2020-04-08 00:00' => 45.0,
                    '2020-04-09 00:00' => 46.0,
                ],
                'startDate' => '2020-04-04 00:00',
                'endDate' => '2020-04-08 00:00',
                'avg' => 43.0,
                'min' => 41.0,
                'max' => 45.0,
                'sum' => 215.0,
                'nbInfValue' => 44,
                'nbInfResult' => 4,
            ]],
            [[
                'values' => [
                    '2020-03-04 00:00' => 40.0,
                    '2020-04-04 00:00' => 41.0,
                    '2020-05-04 00:00' => 42.0,
                    '2020-06-04 00:00' => 43.0,
                    '2020-07-04 00:00' => 44.0,
                    '2020-08-04 00:00' => 45.0,
                    '2020-09-04 00:00' => 46.0,
                ],
                'startDate' => '2020-04-04 00:00',
                'endDate' => '2020-08-04 00:00',
                'avg' => 43.0,
                'min' => 41.0,
                'max' => 45.0,
                'sum' => 215.0,
                'nbInfValue' => 44,
                'nbInfResult' => 4,
            ]],
        ];
    }

    /**
     * @dataProvider provideDataValue
     */
    public function testValuesFunctions($data)
    {
        $entityManager = $this->getEntityManager();
        $dataValueRepository = $this->getDataValueRepository();

        $place = $this->createPersistedPlace();
        $feedData = $this->createPersistedFeedData([], ['place' => $place]);

        foreach ($data['values'] as $date => $value) {
            $dataValueRepository->updateOrCreateValue(
                $feedData,
                new \DateTimeImmutable($date),
                DataValue::FREQUENCY['DAY'],
                $value
            );
        }

        $entityManager->flush();
        $entityManager->clear();

        $startDate = new \DateTimeImmutable($data['startDate']);
        $endDate = new \DateTimeImmutable($data['endDate']);

        $avg = $dataValueRepository->getAverageValue($startDate, $endDate, $feedData, DataValue::FREQUENCY['DAY']);
        self::assertEquals($avg[0]['value'], $data['avg']);

        $min = $dataValueRepository->getMinValue($startDate, $endDate, $feedData, DataValue::FREQUENCY['DAY']);
        self::assertEquals($min[0]['value'], $data['min']);

        $max = $dataValueRepository->getMaxValue($startDate, $endDate, $feedData, DataValue::FREQUENCY['DAY']);
        self::assertEquals($max[0]['value'], $data['max']);

        $sum = $dataValueRepository->getSumValue($startDate, $endDate, $feedData, DataValue::FREQUENCY['DAY']);
        self::assertEquals($sum[0]['value'], $data['sum']);

        $xy = $dataValueRepository->getXY($startDate, $endDate, $feedData, $feedData, DataValue::FREQUENCY['DAY']);
        self::assertSame(\count($xy), 5);
        self::assertEquals($xy[0]['date'], $startDate);
        self::assertSame($xy[0]['xValue'], $data['values'][$data['startDate']]);
        self::assertSame($xy[0]['yValue'], $data['values'][$data['startDate']]);
        self::assertEquals($xy[4]['date'], $endDate);
        self::assertSame($xy[4]['xValue'], $data['values'][$data['endDate']]);
        self::assertSame($xy[4]['yValue'], $data['values'][$data['endDate']]);

        $nbInf = $dataValueRepository->getNumberInfValue($startDate, $endDate, $feedData, DataValue::FREQUENCY['DAY'], $data['nbInfValue']);
        self::assertEquals($nbInf[0]['value'], $data['nbInfResult']);

        $lastValue = $dataValueRepository->getLastValue($feedData, DataValue::FREQUENCY['DAY']);
        $dates = \array_keys($data['values']);
        self::assertEquals(new \DateTimeImmutable($lastValue[0]['date']), new \DateTimeImmutable(\end($dates)));

        $values = $dataValueRepository->getValue($startDate, $endDate, $feedData, DataValue::FREQUENCY['DAY']);
        self::assertSame(\count($values), 5);
        self::assertEquals($values[0]->getDate(), $startDate);
        self::assertSame($values[0]->getValue(), $data['values'][$data['startDate']]);
        self::assertEquals($values[4]->getDate(), $endDate);
        self::assertSame($values[4]->getValue(), $data['values'][$data['endDate']]);

        $repartition = $dataValueRepository->getRepartitionValue($startDate, $endDate, $feedData, 'week', 'weekDay', DataValue::FREQUENCY['DAY'], DataController::YEAR_VERTICAL_REPARTITION);
        self::assertEquals(\count($repartition), 5);
        self::assertEquals($repartition[0]['year'], $startDate->format('Y'));
        self::assertEquals($repartition[0]['axeX'], $startDate->format('W'));
        self::assertEquals($repartition[0]['axeY'], $startDate->format('w') - 1);
        self::assertEquals($repartition[0]['value'], $data['values'][$data['startDate']]);
        self::assertEquals($repartition[4]['year'], $endDate->format('Y'));
        self::assertEquals($repartition[4]['axeX'], $endDate->format('W'));
        self::assertEquals($repartition[4]['axeY'], $endDate->format('w') - 1);
        self::assertEquals($repartition[4]['value'], $data['values'][$data['endDate']]);

        $sum = $dataValueRepository->getSumValueGroupBy($startDate, $endDate, $feedData, DataValue::FREQUENCY['DAY'], 'weekDay');
        self::assertSame(\count($values), 5);
        self::assertEquals($values[0]->getDate(), $startDate);
        self::assertSame($values[0]->getValue(), $data['values'][$data['startDate']]);
        self::assertEquals($values[4]->getDate(), $endDate);
        self::assertSame($values[4]->getValue(), $data['values'][$data['endDate']]);

        $amplitude = $dataValueRepository->getPeriodDataAmplitude($place);
        self::assertEquals(new \DateTimeImmutable($amplitude[1]), new \DateTimeImmutable(\reset($dates)));
        self::assertEquals(new \DateTimeImmutable($amplitude[2]), new \DateTimeImmutable(\end($dates)));
    }
}
