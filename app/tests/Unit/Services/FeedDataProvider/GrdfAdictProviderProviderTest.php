<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\FeedDataProvider;

use Aeneria\GrdfAdictApi\Client\MockGrdfAdictClient;
use App\Services\FeedDataProvider\GrdfAdictProvider;
use App\Services\NotificationService;
use App\Tests\AppTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class GrdfAdictProviderProviderTest extends AppTestCase
{
    private function createDataProvider(): GrdfAdictProvider
    {
        return new GrdfAdictProvider(
            $this->getEntityManager(),
            $this->getFeedRepository(),
            $this->getFeedDataRepository(),
            $this->getDataValueRepository(),
            new MockGrdfAdictClient(),
            $this->createMock(NotificationService::class),
            $this->createMock(SerializerInterface::class),
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
