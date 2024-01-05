<?php

declare(strict_types=1);

namespace App\Services\FeedDataProvider;

use App\Entity\Feed;

class FeedDataProviderFactory
{
    public function __construct(
        private EnedisDataConnectProvider $enedisDataConnectProvider,
        private ProxifiedEnedisDataConnectProvider $proxifiedEnedisDataConnectProvider,
        private MeteoFranceDataProvider $meteoFranceDataProvider,
        private GrdfAdictProvider $grdfAdictProvider,
        private ProxifiedGrdfAdictProvider $proxifiedGrdfAdictProvider,
        private FakeDataProvider $fakeDataProvider
    ) {}

    /**
     * @param Feed[] $feeds
     */
    public function fromFeeds(array $feeds): FeedDataProviderInterface
    {
        $feedDataProviderType = '';

        // Determine Feeds type
        foreach ($feeds as $feed) {
            if (!$feedDataProviderType) {
                $feedDataProviderType = $feed->getFeedDataProviderType();
            } elseif ($feed->getFeedDataProviderType() !== $feedDataProviderType) {
                throw new \InvalidArgumentException("Should be an array of Feeds with the same data provider type here !");
            }
        }

        return $this->fromType($feedDataProviderType);
    }

    public function fromFeed(Feed $feed): FeedDataProviderInterface
    {
        return $this->fromType($feed->getFeedDataProviderType());
    }

    public function fromType(string $feedDataProviderType): FeedDataProviderInterface
    {
        switch ($feedDataProviderType) {
            case Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT_PROXIFIED:
                return $this->proxifiedEnedisDataConnectProvider;
            case Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT:
                return $this->enedisDataConnectProvider;
            case Feed::FEED_DATA_PROVIDER_METEO_FRANCE:
                return $this->meteoFranceDataProvider;
            case Feed::FEED_DATA_PROVIDER_GRDF_ADICT:
                return $this->grdfAdictProvider;
            case Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED:
                return $this->proxifiedGrdfAdictProvider;
            case Feed::FEED_DATA_PROVIDER_FAKE:
                return $this->fakeDataProvider;
            default:
                throw new \InvalidArgumentException("There's no data provider of type : " . $feedDataProviderType);
        }
    }

    /**
     * List all available providers
     */
    public static function listProviders(): array
    {
        return [
            Feed::FEED_DATA_PROVIDER_METEO_FRANCE,
            Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT_PROXIFIED,
            Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT,
            Feed::FEED_DATA_PROVIDER_GRDF_ADICT,
            Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED,
            // This one should not be present in prod
            // so we don't put it here
            // Feed::FEED_DATA_PROVIDER_FAKE,
        ];
    }
}
