<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\FeedDataProvider;

use Aeneria\EnedisDataConnectApi\Client\MockDataConnectClient;
use Aeneria\EnedisDataConnectApi\Model\Address;
use Aeneria\EnedisDataConnectApi\Model\Token;
use App\Entity\Feed;
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use App\Services\NotificationService;
use App\Tests\AppTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class EnedisDataConnectDataProviderTest extends AppTestCase
{
    private function createEnedisDataConnectDataProvider(
        SerializerInterface $serializer = null,
        RouterInterface $router = null
    ): EnedisDataConnectProvider {
        return new EnedisDataConnectProvider(
            false,
            $this->getEntityManager(),
            $this->getFeedRepository(),
            $this->getFeedDataRepository(),
            $this->getDataValueRepository(),
            new MockDataConnectClient(),
            $router ?? $this->createMock(RouterInterface::class),
            $serializer ?? $this->createMock(SerializerInterface::class),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );
    }

    private function createEnedisDataConnectFeed(SerializerInterface $serializer, Token $token = null, Address $address = null): Feed
    {
        $feedRepository = $this->getFeedRepository();
        $entityManager = $this->getEntityManager();
        $feed = $this->createPersistedFeed([
            'feedType' => Feed::FEED_TYPE_ELECTRICITY,
            'feedDataProviderType' => Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT,
            'param' => [
                'TOKEN' => $token ? $serializer->serialize($token, 'json') : '',
                'ADDRESS' => $address ? $serializer->serialize($address, 'json') : '',
            ],
        ]);
        $entityManager->flush();

        $feedRepository->createDependentFeedData($feed);
        $entityManager->flush();
        $entityManager->clear();

        return $feedRepository->findOneById($feed->getId());
    }

    public function testIsAvailableDataDate()
    {
        $dataProvider = $this->createEnedisDataConnectDataProvider();

        $this->assertTrue($dataProvider::isAvailableDataDate(new \DateTimeImmutable('2 days ago')));
        $this->assertTrue($dataProvider::isAvailableDataDate(new \DateTimeImmutable('yesterday')));
        $this->assertFalse($dataProvider::isAvailableDataDate(new \DateTimeImmutable('now')));
    }
}
