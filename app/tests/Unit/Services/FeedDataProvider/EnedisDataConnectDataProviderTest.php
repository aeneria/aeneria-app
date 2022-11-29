<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\FeedDataProvider;

use Aeneria\EnedisDataConnectApi\Model\Address;
use Aeneria\EnedisDataConnectApi\Model\Token;
use Aeneria\EnedisDataConnectApi\Service\MockDataConnectService;
use App\Entity\Feed;
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use App\Services\NotificationService;
use App\Tests\AppTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class EnedisDataConnectDataProviderTest extends AppTestCase
{
    private function createEnedisDataConnectDataProvider(?SerializerInterface $serializer = null): EnedisDataConnectProvider
    {
        return new EnedisDataConnectProvider(
            $this->getEntityManager(),
            $this->getFeedRepository(),
            $this->getFeedDataRepository(),
            $this->getDataValueRepository(),
            new MockDataConnectService(),
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

    public function testGetTokenFromFeed()
    {
        $token = (new Token())
            ->setAccessToken('access_token')
            ->setTokenType('token_type')
            ->setScope('scope')
            ->setAccessTokenIssuedAt(new \DateTimeImmutable('tomorrow midnight'))
            ->setAccessTokenExpirationDate(new \DateTimeImmutable('next month midnight'))
            ->setRefreshToken('refresh_token')
            ->setRefreshTokenIssuedAt(new \DateTimeImmutable('tomorrow midnight'))
            ->setUsagePointsId('usage_point')
        ;

        $serializer = $this->createMock(SerializerInterface::class);
        \assert($serializer instanceof SerializerInterface);
        $serializer
            ->expects($this->exactly(1))
            ->method('serialize')
            ->willReturn('token')
        ;
        $serializer
            ->expects($this->exactly(1))
            ->method('deserialize')
            ->willReturn($token)
        ;

        $feed = $this->createEnedisDataConnectFeed($serializer, $token);

        $dataProvider = $this->createEnedisDataConnectDataProvider($serializer);

        $tokenFromFeed = $dataProvider->getTokenFrom($feed);

        self::assertEquals($token, $tokenFromFeed);
    }

    public function testGetAdressFromFeed()
    {
        $address = (new Address())
            ->setCustomerId('customrerID')
            ->setUsagePointId('usagePointId')
            ->setUsagePointStatus('usagePointStatut')
            ->setMeterType('meterType')
            ->setStreet('street')
            ->setLocality('locality')
            ->setPostalCode('postalCode')
            ->setInseeCode('inseeCode')
            ->setCity('city')
            ->setCountry('country')
            ->setLatitude(12.5)
            ->setLongitude(13.6)
            ->setAltitude(-5.2)
        ;

        $serializer = $this->createMock(SerializerInterface::class);
        \assert($serializer instanceof SerializerInterface);
        $serializer
            ->expects($this->any())
            ->method('serialize')
            ->willReturn('adress')
        ;
        $serializer
            ->expects($this->any())
            ->method('deserialize')
            ->willReturn($address)
        ;

        $feed = $this->createEnedisDataConnectFeed($serializer, null, $address);

        $dataProvider = $this->createEnedisDataConnectDataProvider($serializer);

        $adressFromFeed = $dataProvider->getAddressFrom($feed);

        self::assertEquals($address, $adressFromFeed);
    }

    public function testIsAvailableDataDate()
    {
        $dataProvider = $this->createEnedisDataConnectDataProvider();

        $this->assertTrue($dataProvider::isAvailableDataDate(new \DateTimeImmutable('2 days ago')));
        $this->assertTrue($dataProvider::isAvailableDataDate(new \DateTimeImmutable('yesterday')));
        $this->assertFalse($dataProvider::isAvailableDataDate(new \DateTimeImmutable('now')));
    }

    public function testFetchDataWithValidToken()
    {
        $token = (new Token())
            ->setAccessToken('access_token')
            ->setTokenType('token_type')
            ->setScope('scope')
            ->setAccessTokenIssuedAt(new \DateTimeImmutable('tomorrow midnight'))
            ->setAccessTokenExpirationDate(new \DateTimeImmutable('next month midnight'))
            ->setRefreshToken('refresh_token')
            ->setRefreshTokenIssuedAt(new \DateTimeImmutable('tomorrow midnight'))
            ->setUsagePointsId('usage_point')
        ;

        $serializer = $this->createMock(SerializerInterface::class);
        \assert($serializer instanceof SerializerInterface);
        $serializer
            ->expects($this->any())
            ->method('serialize')
            ->willReturn('token')
        ;
        $serializer
            ->expects($this->any())
            ->method('deserialize')
            ->willReturn($token)
        ;

        $feed = $this->createEnedisDataConnectFeed($serializer, $token);

        $dataProvider = $this->createEnedisDataConnectDataProvider($serializer);

        self::assertFalse($this->getFeedRepository()->isUpToDate($feed, $date = new \DateTimeImmutable('today midnight'), Feed::getFrequenciesFor(Feed::FEED_TYPE_ELECTRICITY)));

        $dataProvider->fetchData($date, [$feed]);

        self::assertTrue($this->getFeedRepository()->isUpToDate($feed, $date, Feed::getFrequenciesFor(Feed::FEED_TYPE_ELECTRICITY)));
    }

    public function testFetchDataWithUnvalidToken()
    {
        $token = (new Token())
            ->setAccessToken('access_token')
            ->setTokenType('token_type')
            ->setScope('scope')
            ->setAccessTokenIssuedAt(new \DateTimeImmutable('tomorrow midnight'))
            ->setAccessTokenExpirationDate(new \DateTimeImmutable('yesterday'))
            ->setRefreshToken('refresh_token')
            ->setRefreshTokenIssuedAt(new \DateTimeImmutable('tomorrow midnight'))
            ->setUsagePointsId('usage_point')
        ;

        $serializer = $this->createMock(SerializerInterface::class);
        \assert($serializer instanceof SerializerInterface);
        $serializer
            ->expects($this->any())
            ->method('serialize')
            ->willReturn('token')
        ;
        $serializer
            ->expects($this->any())
            ->method('deserialize')
            ->willReturn($token)
        ;

        $feed = $this->createEnedisDataConnectFeed($serializer, $token);

        $dataProvider = $this->createEnedisDataConnectDataProvider($serializer);

        self::assertFalse($this->getFeedRepository()->isUpToDate($feed, $date = new \DateTimeImmutable('today midnight'), Feed::getFrequenciesFor(Feed::FEED_TYPE_ELECTRICITY)));

        $dataProvider->fetchData($date, [$feed]);

        self::assertTrue($this->getFeedRepository()->isUpToDate($feed, $date, Feed::getFrequenciesFor(Feed::FEED_TYPE_ELECTRICITY)));
    }
}
