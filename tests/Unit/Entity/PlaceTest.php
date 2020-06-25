<?php

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

        self::assertTrue(\in_array($feed, $place->getFeeds()));
        self::assertTrue(\in_array($feed2, $place->getFeeds()));

        self::assertNotNull($place->getFeed(Feed::FEED_TYPE_ELECTRICITY));

        self::assertCount(0, $place->getFeedDatas());
        self::assertNull($place->getFeedData(FeedData::FEED_DATA_CONSO_ELEC));
    }

    public function testPlaceSetAllowedUsers()
    {
        $user = $this->createUser();
        $user2 = $this->createUser();

        $place = $this
            ->createPlace()
            ->setAllowedUsers([$user, $user2])
        ;

        self::assertTrue(\in_array($user, $place->getAllowedUsers()));
        self::assertTrue(\in_array($user2, $place->getAllowedUsers()));
    }
}
