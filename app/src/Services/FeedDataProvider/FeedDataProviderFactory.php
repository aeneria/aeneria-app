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

    /** @var GrdfAdictProvider */
    private $grdfAdictProvider;

    /** @var ProxifiedGrdfAdictProvider */
    private $proxifiedGrdfAdictProvider;

    /** @var FakeDataProvider */
    private $fakeDataProvider;

    public function __construct(
        EnedisDataConnectProvider $enedisDataConnectProvider,
        LinkyDataProvider $linkyDataProvider,
        MeteoFranceDataProvider $meteoFranceDataProvider,
        GrdfAdictProvider $grdfAdictProvider,
        ProxifiedGrdfAdictProvider $proxifiedGrdfAdictProvider,
        FakeDataProvider $fakeDataProvider
    ) {
        $this->enedisDataConnectProvider = $enedisDataConnectProvider;
        $this->linkyDataProvider = $linkyDataProvider;
        $this->meteoFranceDataProvider = $meteoFranceDataProvider;
        $this->grdfAdictProvider = $grdfAdictProvider;
        $this->proxifiedGrdfAdictProvider = $proxifiedGrdfAdictProvider;
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
    public static function listProviders(): array {
        return [
            Feed::FEED_DATA_PROVIDER_METEO_FRANCE,
            Feed::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT,
            Feed::FEED_DATA_PROVIDER_GRDF_ADICT,
            Feed::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED,
            // This one should not be present in prod
            // so we don't put it here
            // Feed::FEED_DATA_PROVIDER_FAKE,
            // This one is depracted
            // Feed::FEED_DATA_PROVIDER_LINKY,
        ];
    }
}
