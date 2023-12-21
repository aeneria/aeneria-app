<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Tests\AppTestCase;

final class PlaceRepositoryTest extends AppTestCase
{
    public function testPersistAndFind()
    {
        $entityManager = $this->getEntityManager();
        $placeRepository = $this->getPlaceRepository();

        $place = $this->createPersistedPlace();
        $entityManager->flush();
        $entityManager->clear();

        $placeFromRepo = $placeRepository->find($place->getId());

        self::assertSame($place->getId(), $placeFromRepo->getId());
    }

    public function testPurge()
    {
        $entityManager = $this->getEntityManager();
        $placeRepository = $this->getPlaceRepository();

        $place = $this->createPersistedPlace();
        $feed = $this->createFeed();
        $place->addFeed($feed);

        $entityManager->flush();
        $entityManager->clear();

        $placeRepository->purge($place);

        self::assertNull($placeRepository->find($place->getId()));
        self::assertNull($this->getFeedRepository()->find($feed->getId()));
    }

    public function testPurgePlaceWithFeedUsedElswhereDontPurgeFeed()
    {
        $entityManager = $this->getEntityManager();
        $placeRepository = $this->getPlaceRepository();

        $place1 = $this->createPersistedPlace();
        $place2 = $this->createPersistedPlace();
        $feed = $this->createFeed();
        $place1->addFeed($feed);
        $place2->addFeed($feed);

        $entityManager->flush();
        $entityManager->clear();

        $placeRepository->purge($place1);

        self::assertNull($placeRepository->find($place1->getId()));
        self::assertNotNull($placeRepository->find($place2->getId()));
        self::assertNotNull($this->getFeedRepository()->find($feed->getId()));
    }

    public function testAllowedPlaces()
    {
        $entityManager = $this->getEntityManager();
        $placeRepository = $this->getPlaceRepository();

        $user1 = $this->createPersistedUser();
        $place1 = $this->createPersistedPlace([
            'user' => $user1,
        ]);

        $user2 = $this->createPersistedUser();

        $place2 = $this->createPersistedPlace([
            'user' => $user2,
            'public' => true,
        ]);

        $place3 = $this->createPersistedPlace([
            'user' => $user2,
            'allowedUsers' => [$user1],
        ]);

        $place4 = $this->createPersistedPlace([
            'user' => $user2,
            'public' => false,
        ]);

        $entityManager->flush();
        $entityManager->clear();

        $allowedPlaces = $placeRepository->getAllowedPlaces($user1);

        self::assertTrue(\key_exists($place1->getId(), $allowedPlaces));
        self::assertTrue(\key_exists($place2->getId(), $allowedPlaces));
        self::assertTrue(\key_exists($place3->getId(), $allowedPlaces));
        self::assertTrue(!\key_exists($place4->getId(), $allowedPlaces));
    }
}
