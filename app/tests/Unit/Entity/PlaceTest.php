<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Feed;
use App\Entity\FeedData;
use App\Tests\AppTestCase;

final class PlaceTest extends AppTestCase
{
    public function testPlaceInstance()
    {
        $user = $this->createUser();

        $place = $this->createPlace([
            'id' => $placeId = \rand(),
            'name' => $name = 'test' . \rand(),
            'icon' => 'toto',
            'public' => false,
            'user' => $user,
        ]);

        self::assertSame($place->getId(), $placeId);
        self::assertSame($place->getName(), $name);
        self::assertSame($place->getIcon(), 'toto');
        self::assertSame($place->isPublic(), false);
        self::assertSame($place->getUser(), $user);
    }

    public function testPlaceAddFeeds()
    {
        $feed = $this->createFeed(['feedType' => Feed::FEED_TYPE_ELECTRICITY]);
        $feed2 = $this->createFeed(['feedType' => Feed::FEED_TYPE_METEO]);

        $place = $this
            ->createPlace([
                'feeds' => [$feed],
            ])
            ->addFeed($feed2)
        ;

        self::assertTrue($place->getFeeds()->contains($feed));
        self::assertTrue($place->getFeeds()->contains($feed2));

        self::assertNotNull($place->getFeed(Feed::FEED_TYPE_ELECTRICITY));

        self::assertCount(0, $place->getFeedDatas());
        self::assertNull($place->getFeedData(FeedData::FEED_DATA_CONSO_ELEC));

        $place->removeFeed($feed2);
        self::assertFalse($place->getFeeds()->contains($feed2));

        $place->addFeed($feed2);
        self::assertTrue($place->getFeeds()->contains($feed));
    }

    public function testPlaceSetAllowedUsers()
    {
        $user = $this->createUser();
        $user2 = $this->createUser();

        $place = $this
            ->createPlace()
            ->setAllowedUsers([$user, $user2])
        ;

        self::assertTrue($place->getAllowedUsers()->contains($user));
        self::assertTrue($place->getAllowedUsers()->contains($user2));
    }
}
