<?php

namespace App\Services\FeedDataProvider;

use App\Entity\Feed;

class FeedDataProviderFactory
{
    /** @var EnedisDataConnectProvider */
    private $enedisDataConnectProvider;

    /** @deprecated @var LinkyDataProvider */
    private $linkyDataProvider;

    /** @var MeteoFranceDataProvider */
    private $meteoFranceDataProvider;

    /** @var FakeDataProvider */
    private $fakeDataProvider;

    public function __construct(
        EnedisDataConnectProvider $enedisDataConnectProvider,
        LinkyDataProvider $linkyDataProvider,
        MeteoFranceDataProvider $meteoFranceDataProvider,
        FakeDataProvider $fakeDataProvider
    ) {
        $this->enedisDataConnectProvider = $enedisDataConnectProvider;
        $this->linkyDataProvider = $linkyDataProvider;
        $this->meteoFranceDataProvider = $meteoFranceDataProvider;
        $this->fakeDataProvider = $fakeDataProvider;
    }

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
            case Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT:
                return $this->enedisDataConnectProvider;
            case Feed::FEED_DATA_PROVIDER_LINKY:
                return $this->linkyDataProvider;
            case Feed::FEED_DATA_PROVIDER_METEO_FRANCE:
                return $this->meteoFranceDataProvider;
            case Feed::FEED_DATA_PROVIDER_FAKE:
                return $this->fakeDataProvider;
            default:
                throw new \InvalidArgumentException("There's no data provider of type : " . $feedDataProviderType);
        }
    }
}
