<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\FeedData;
use App\Tests\AppTestCase;

final class FeedDataRepositoryTest extends AppTestCase
{
    public function testPersistAndFind()
    {
        $entityManager = $this->getEntityManager();
        $feedDataRepository = $this->getFeedDataRepository();

        $feedData = $this->createPersistedFeedData();

        $entityManager->flush();
        $entityManager->clear();

        $feedDataFromRepo = $feedDataRepository->find($feedData->getId());

        self::assertSame($feedData->getId(), $feedDataFromRepo->getId());
    }

    public function testPurge()
    {
        $entityManager = $this->getEntityManager();
        $feedDataRepository = $this->getFeedDataRepository();

        $feedData = $this->createPersistedFeedData();

        $entityManager->flush();
        $entityManager->clear();

        $feedDataRepository->purge($feedData);

        self::assertNull($feedDataRepository->find($feedData->getId()));
    }

    public function testFindOneByPlaceAndDataType()
    {
        $entityManager = $this->getEntityManager();
        $feedDataRepository = $this->getFeedDataRepository();

        $place = $this->createPersistedPlace();

        $feedData = $this->createPersistedFeedData(
            [
                'dataType' => FeedData::FEED_DATA_TEMPERATURE,
            ],
            [
                'place' => $place,
            ]
        );

        $entityManager->flush();
        $entityManager->clear();

        $feedDataFromRepo = $feedDataRepository->findOneByPlaceAndDataType($place, FeedData::FEED_DATA_TEMPERATURE);

        self::assertSame($feedData->getId(), $feedDataFromRepo->getId());
    }
}
