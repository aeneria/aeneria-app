<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\FeedDataProvider;

use Aeneria\EnedisDataConnectApi\Model\Address;
use Aeneria\EnedisDataConnectApi\Model\Token;
use Aeneria\EnedisDataConnectApi\Service\MockDataConnectService;
use App\Entity\Feed;
use App\Services\FeedDataProvider\EnedisDataConnectProvider;
use App\Tests\AppTestCase;

class EnedisDataConnectDataProviderTest extends AppTestCase
{

    private function createEnedisDataConnectDataProvider() : EnedisDataConnectProvider
    {
        return new EnedisDataConnectProvider(
            $this->getEntityManager(),
            $this->getFeedRepository(),
            $this->getFeedDataRepository(),
            $this->getDataValueRepository(),
            new MockDataConnectService(),
            $this->getSerializer()
        );
    }

    private function createEnedisDataConnectFeed(Token $token = null, Address $address = null): Feed
    {
        $serializer = $this->getSerializer();
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

        $feed = $this->createEnedisDataConnectFeed($token);

        $dataProvider = $this->createEnedisDataConnectDataProvider();

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

        $feed = $this->createEnedisDataConnectFeed(null, $address);

        $dataProvider = $this->createEnedisDataConnectDataProvider();

        $adressFromFeed = $dataProvider->getAddressFrom($feed);

        self::assertEquals($address, $adressFromFeed);
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

        $feed = $this->createEnedisDataConnectFeed($token);

        $dataProvider = $this->createEnedisDataConnectDataProvider();

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

        $feed = $this->createEnedisDataConnectFeed($token);

        $dataProvider = $this->createEnedisDataConnectDataProvider();

        self::assertFalse($this->getFeedRepository()->isUpToDate($feed, $date = new \DateTimeImmutable('today midnight'), Feed::getFrequenciesFor(Feed::FEED_TYPE_ELECTRICITY)));

        $dataProvider->fetchData($date, [$feed]);

        self::assertTrue($this->getFeedRepository()->isUpToDate($feed, $date, Feed::getFrequenciesFor(Feed::FEED_TYPE_ELECTRICITY)));
    }
}
