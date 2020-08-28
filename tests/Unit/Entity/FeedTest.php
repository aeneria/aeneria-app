<?php

namespace App\Tests\Unit\Entity;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Tests\AppTestCase;

final class FeedTest extends AppTestCase
{
    public function testFeedInstance()
    {
        $place = $this->createPlace();
        $feed = $this->createFeed([
            'id' => $feedId = \rand(),
            'name' => $name = 'test' . \rand(),
            'feedType' => Feed::FEED_TYPE_ELECTRICITY,
            'feedDataProviderType' => Feed::FEED_DATA_PROVIDER_LINKY,
            'param' => ['toto' => 'toto'],
            'place' => $place,
        ]);

        self::assertSame($feed->getId(), $feedId);
        self::assertSame($feed->getName(), $name);
        self::assertSame($feed->getFeedType(), Feed::FEED_TYPE_ELECTRICITY);
        self::assertSame($feed->getFeedDataProviderType(), Feed::FEED_DATA_PROVIDER_LINKY);
        self::assertSame($feed->getParam(), ['toto' => 'toto']);
        self::assertSame($feed->getPlace(), $place);
        self::assertSame($feed->getFrequencies(), DataValue::getAllFrequencies());
    }

    public function testAllowedDataProvidersFor()
    {
        self::assertSame(Feed::getAllowedDataProvidersFor(Feed::FEED_TYPE_ELECTRICITY), [Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT]);
        self::assertSame(Feed::getAllowedDataProvidersFor(Feed::FEED_TYPE_METEO), [Feed::FEED_DATA_PROVIDER_METEO_FRANCE]);

        $this->expectExceptionMessageRegExp('/Feed type .* does not exist !/');
        Feed::getNameFor('Toto');
    }

    public function testNameFor()
    {
        self::assertSame(Feed::getNameFor(Feed::FEED_TYPE_ELECTRICITY), 'Électricité');
        self::assertSame(Feed::getNameFor(Feed::FEED_TYPE_METEO), 'Météo');

        $this->expectExceptionMessageRegExp('/Feed type .* does not exist !/');
        Feed::getNameFor('Toto');
    }

    public function testFrequenciesFor()
    {
        self::assertSame(Feed::getFrequenciesFor(
            Feed::FEED_TYPE_ELECTRICITY),
            DataValue::getAllFrequencies()
        );

        self::assertSame(
            Feed::getFrequenciesFor(Feed::FEED_TYPE_METEO),
            [
                DataValue::FREQUENCY_DAY,
                DataValue::FREQUENCY_WEEK,
                DataValue::FREQUENCY_MONTH,
            ]
        );

        $this->expectExceptionMessageRegExp('/Feed type .* does not exist !/');
        Feed::getFrequenciesFor('Toto');
    }

    public function testDataTypeFor()
    {
        self::assertSame(Feed::getDataTypeFor(
            Feed::FEED_TYPE_ELECTRICITY),
            [FeedData::FEED_DATA_CONSO_ELEC]
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

        $this->expectExceptionMessageRegExp('/Feed type .* does not exist !/');
        Feed::getDataTypeFor('Toto');
    }
}
