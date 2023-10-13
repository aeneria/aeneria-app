<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\FeedData;
use App\Tests\AppTestCase;

final class FeedDataTest extends AppTestCase
{
    public function testFeedDataInstance()
    {
        $feed = $this->createFeed();
        $feedData = $this->createFeedData([
            'id' => $feedDataId = \rand(),
            'dataType' => FeedData::FEED_DATA_DJU,
            'feed' => $feed,
        ]);

        self::assertSame($feedData->getId(), $feedDataId);
        self::assertSame($feedData->getDataType(), FeedData::FEED_DATA_DJU);
        self::assertSame($feedData->getFeed(), $feed);
        self::assertSame($feedData->getDisplayDataType(), 'Degrés Jour Unifié');
    }

    public function testUnitFor()
    {
        self::assertSame(FeedData::getUnitFor(FeedData::FEED_DATA_DJU), 'DJU');
        self::assertSame(FeedData::getUnitFor(FeedData::FEED_DATA_NEBULOSITY), '%');

        self::assertSame(FeedData::getUnitFor('toto'), '');
    }

    public function testLabelFor()
    {
        self::assertSame(FeedData::getLabelFor(FeedData::FEED_DATA_DJU), 'Degrés Jour Unifié');
        self::assertSame(FeedData::getLabelFor(FeedData::FEED_DATA_NEBULOSITY), 'Nébulosité');
    }
}
