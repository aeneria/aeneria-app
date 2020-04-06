<?php

namespace App\Tests\Unit\Entity;

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
        $feed = $this->createFeed();
        $feed2 = $this->createFeed();

        $place = $this
            ->createPlace([
                'feeds' => [$feed],
            ])
            ->addFeed($feed2)
        ;

        self::assertTrue(\in_array($feed, $place->getFeeds()));
        self::assertTrue(\in_array($feed2, $place->getFeeds()));
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