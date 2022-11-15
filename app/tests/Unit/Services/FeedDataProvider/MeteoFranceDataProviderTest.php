<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\FeedDataProvider;

use App\Entity\Feed;
use App\Services\FeedDataProvider\MeteoFranceDataProvider;
use App\Services\NotificationService;
use App\Tests\AppTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MeteoFranceDataProviderTest extends AppTestCase
{
    private function createMeteoFranceDataProvider(HttpClientInterface $httpClient = null): MeteoFranceDataProvider
    {
        return new MeteoFranceDataProvider(
            $this->getParameter('kernel.project_dir'),
            $this->getEntityManager(),
            $this->getFeedRepository(),
            $this->getFeedDataRepository(),
            $this->getDataValueRepository(),
            $this->createMock(NotificationService::class),
            $httpClient ?? HttpClient::create(),
            $this->getLogger()
        );
    }

    private function createMeteoFeed(): Feed
    {
        $entityManager = $this->getEntityManager();
        $feedRepository = $this->getFeedRepository();
        $feed = $this->createPersistedFeed([
            'feedType' => Feed::FEED_TYPE_METEO,
            'feedDataProviderType' => Feed::FEED_DATA_PROVIDER_METEO_FRANCE,
            'param' => ['STATION_ID' => 7005],
        ]);
        $entityManager->flush();

        $feedRepository->createDependentFeedData($feed);
        $entityManager->flush();
        $entityManager->clear();

        return $feedRepository->findOneById($feed->getId());
    }

    public function testAvailableStation()
    {
        $dataProvider = $this->createMeteoFranceDataProvider();

        $availableStations = $dataProvider->getAvailableStations();

        self::assertCount(62, $availableStations);
        $ajaccioStations = \array_filter($availableStations, function ($station) {
            return 'Ajaccio' === $station->label;
        });

        self::assertEquals('7761', \reset($ajaccioStations)->key);
    }

    public function testFetchData()
    {
        $feed = $this->createMeteoFeed();

        $responses = [];
        for ($i = 0; $i < 8; ++$i) {
            $responses[] = new MockResponse(<<<BODY
            numer_sta;t;pres;u;n;rr3
            07005;12;102130;55;51;2
            BODY);
        }

        $httpClient = new MockHttpClient($responses);
        $dataProvider = $this->createMeteoFranceDataProvider($httpClient);

        self::assertFalse($this->getFeedRepository()->isUpToDate($feed, $date = new \DateTimeImmutable('today midnight'), Feed::getFrequenciesFor(Feed::FEED_TYPE_METEO)));

        $dataProvider->fetchData($date, [$feed]);

        self::assertTrue($this->getFeedRepository()->isUpToDate($feed, $date, Feed::getFrequenciesFor(Feed::FEED_TYPE_METEO)));
    }
}
