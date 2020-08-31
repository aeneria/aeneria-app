<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Feed;
use App\Entity\Place;
use App\Tests\AppTestCase;

final class FeedRepositoryTest extends AppTestCase
{
    public function testPersistAndFind()
    {
        $entityManager = $this->getEntityManager();
        $feedRepository = $this->getFeedRepository();

        $feed = $this->createPersistedFeed();

        $entityManager->flush();
        $entityManager->clear();

        $feedFromRepo = $feedRepository->find($feed->getId());

        self::assertSame($feed->getId(), $feedFromRepo->getId());
    }

    public function testPurge()
    {
        $entityManager = $this->getEntityManager();
        $feedRepository = $this->getFeedRepository();

        $feed = $this->createPersistedFeed();

        $entityManager->flush();
        $entityManager->clear();

        $feedRepository->purge($feed);

        self::assertNull($feedRepository->find($feed->getId()));
    }

    public function testCreateDependentFeedData()
    {
        $entityManager = $this->getEntityManager();
        $feedRepository = $this->getFeedRepository();
        $feedDataRepository = $this->getFeedDataRepository();

        $feed = $this->createPersistedFeed([
            'feedType' => Feed::FEED_TYPE_METEO,
        ]);
        $feedRepository->createDependentFeedData($feed);

        $entityManager->flush();
        $entityManager->clear();

        $feeds = $feedDataRepository->findByFeed($feed);

        self::assertTrue(8 === \count($feeds));
    }

    public function testfindAllActive()
    {
        $entityManager = $this->getEntityManager();
        $feedRepository = $this->getFeedRepository();

        $feed = $this->createPersistedFeed([
            'feedDataProviderType' => Feed::FEED_DATA_PROVIDER_METEO_FRANCE,
        ]);

        $entityManager->flush();
        $entityManager->clear();

        $feeds = $feedRepository->findAllActive(Feed::FEED_DATA_PROVIDER_METEO_FRANCE);

        self::assertArrayHasKey($feed->getId(), $feeds);
    }

    public function testGetOrCreateMeteoFranceFeed() {
        $entityManager = $this->getEntityManager();
        $feedRepository = $this->getFeedRepository();

        $feed1 = $feedRepository->getOrCreateMeteoFranceFeed([
            'STATION_ID' => $stationId = 'toto' . \rand(),
        ]);

        $entityManager->flush();
        $entityManager->clear();

        self::assertSame($stationId, $feed1->getName());

        $feed2 = $feedRepository->getOrCreateMeteoFranceFeed([
            'STATION_ID' => $stationId,
        ]);

        $entityManager->flush();
        $entityManager->clear();

        self::assertSame($feed1->getId(), $feed2->getId());

        $feed3 = $feedRepository->getOrCreateMeteoFranceFeed([
            'STATION_ID' => 'tata' . \rand(),
        ]);

        $entityManager->flush();
        $entityManager->clear();

        self::assertNotSame($feed1->getId(), $feed3->getId());


    }

    public function testFindOrphan() {
        $entityManager = $this->getEntityManager();
        $feedRepository = $this->getFeedRepository();

        $feed = $this->createPersistedFeed(['place' => new Place()]);

        $entityManager->flush();
        $entityManager->clear();

        $orphans = $feedRepository->findOrphans();

        self::assertArrayHasKey($feed->getId(), $orphans);
    }
}
