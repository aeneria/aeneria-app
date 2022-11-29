<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\FeedDataProvider;

use Aeneria\EnedisDataConnectApi\Model\Address;
use Aeneria\EnedisDataConnectApi\Model\Token;
use Aeneria\GrdfAdictApi\Service\MockGrdfAdictService;
use App\Entity\Feed;
use App\Services\FeedDataProvider\GrdfAdictProvider;
use App\Services\NotificationService;
use App\Tests\AppTestCase;

class GrdfAdictProviderProviderTest extends AppTestCase
{
    private function createDataProvider(): GrdfAdictProvider
    {
        return new GrdfAdictProvider(
            $this->getEntityManager(),
            $this->getFeedRepository(),
            $this->getFeedDataRepository(),
            $this->getDataValueRepository(),
            new MockGrdfAdictService(),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );
    }

    public function testIsLastAvailableDataDate()
    {
        $dataProvider = $this->createDataProvider();

        $this->assertTrue($dataProvider::isAvailableDataDate(new \DateTimeImmutable('2 days ago')));
        $this->assertFalse($dataProvider::isAvailableDataDate(new \DateTimeImmutable('yesterday')));
        $this->assertFalse($dataProvider::isAvailableDataDate(new \DateTimeImmutable('now')));
    }
}
