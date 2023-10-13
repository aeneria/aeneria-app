<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Tests\AppTestCase;

final class FeedTest extends AppTestCase
{
    public function testFeedInstance()
    {
        $feed = $this->createFeed([
            'id' => $feedId = \rand(),
            'name' => $name = 'test' . \rand(),
            'feedType' => Feed::FEED_TYPE_ELECTRICITY,
            'feedDataProviderType' => Feed::FEED_DATA_PROVIDER_FAKE,
            'param' => ['toto' => 'toto'],
            'places' => [$place = $this->createPlace()],
        ]);

        self::assertSame($feed->getId(), $feedId);
        self::assertSame($feed->getName(), $name);
        self::assertSame($feed->getFeedType(), Feed::FEED_TYPE_ELECTRICITY);
        self::assertSame($feed->getFeedDataProviderType(), Feed::FEED_DATA_PROVIDER_FAKE);
        self::assertSame($feed->getParam(), ['toto' => 'toto']);
        self::assertSame($feed->getFrequencies(), DataValue::getAllFrequencies());
        self::assertTrue($feed->getPlaces()->contains($place));

        $feed->removePlace($place);
        self::assertCount(0, $feed->getPlaces());

        $feed->addPlace($place);
        self::assertCount(1, $feed->getPlaces());
    }

    public function testAllowedDataProvidersFor()
    {
        self::assertSame(Feed::getAllowedDataProvidersFor(Feed::FEED_TYPE_ELECTRICITY), [Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT]);
        self::assertSame(Feed::getAllowedDataProvidersFor(Feed::FEED_TYPE_METEO), [Feed::FEED_DATA_PROVIDER_METEO_FRANCE]);
        self::assertSame(Feed::getAllowedDataProvidersFor(Feed::FEED_TYPE_GAZ), [
            Feed::FEED_DATA_PROVIDER_GRDF_ADICT,
            Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED,
        ]);

        $this->expectExceptionMessageMatches('/Feed type .* does not exist !/');
        Feed::getNameFor('Toto');
    }

    public function testNameFor()
    {
        self::assertSame(Feed::getNameFor(Feed::FEED_TYPE_ELECTRICITY), 'Électricité');
        self::assertSame(Feed::getNameFor(Feed::FEED_TYPE_METEO), 'Météo');
        self::assertSame(Feed::getNameFor(Feed::FEED_TYPE_GAZ), 'Gaz');

        $this->expectExceptionMessageMatches('/Feed type .* does not exist !/');
        Feed::getNameFor('Toto');
    }

    public function testFrequenciesFor()
    {
        self::assertSame(
            Feed::getFrequenciesFor(
                Feed::FEED_TYPE_ELECTRICITY
            ),
            DataValue::getAllFrequencies()
        );

        self::assertSame(
            Feed::getFrequenciesFor(Feed::FEED_TYPE_GAZ),
            [
                'DAY' => DataValue::FREQUENCY_DAY,
                'WEEK' => DataValue::FREQUENCY_WEEK,
                'MONTH' => DataValue::FREQUENCY_MONTH,
                'YEAR' => DataValue::FREQUENCY_YEAR,
            ]
        );

        self::assertSame(
            Feed::getFrequenciesFor(Feed::FEED_TYPE_METEO),
            [
                'DAY' => DataValue::FREQUENCY_DAY,
                'WEEK' => DataValue::FREQUENCY_WEEK,
                'MONTH' => DataValue::FREQUENCY_MONTH,
                'YEAR' => DataValue::FREQUENCY_YEAR,
            ]
        );

        $this->expectExceptionMessageMatches('/Feed type .* does not exist !/');
        Feed::getFrequenciesFor('Toto');
    }

    public function testDataTypeFor()
    {
        self::assertSame(
            Feed::getDataTypeFor(
                Feed::FEED_TYPE_ELECTRICITY
            ),
            [FeedData::FEED_DATA_CONSO_ELEC]
        );
        self::assertSame(
            Feed::getDataTypeFor(
                Feed::FEED_TYPE_GAZ
            ),
            [FeedData::FEED_DATA_CONSO_GAZ]
        );

        self::assertSame(
            Feed::getDataTypeFor(Feed::FEED_TYPE_METEO),
            [
                FeedData::FEED_DATA_TEMPERATURE,
                FeedData::FEED_DATA_TEMPERATURE_MIN,
                FeedData::FEED_DATA_TEMPERATURE_MAX,
                FeedData::FEED_DATA_DJU,
                FeedData::FEED_DATA_PRESSURE,
                FeedData::FEED_DATA_HUMIDITY,
                FeedData::FEED_DATA_NEBULOSITY,
                FeedData::FEED_DATA_RAIN,
            ]
        );

        $this->expectExceptionMessageMatches('/Feed type .* does not exist !/');
        Feed::getDataTypeFor('Toto');
    }
}
