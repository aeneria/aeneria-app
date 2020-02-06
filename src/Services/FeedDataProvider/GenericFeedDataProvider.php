<?php
namespace App\Services\FeedDataProvider;

use App\Entity\Feed;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;

class GenericFeedDataProvider extends AbstractFeedDataProvider {

    private $linkyDataProvider;
    private $meteoFranceDataProvider;
    private $fakeDataProvider;

    public function __construct(EntityManagerInterface $entityManager, FeedRepository $feedRepository, FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        LinkyDataProvider $linkyDataProvider, MeteoFranceDataProvider $meteoFranceDataProvider, FakeDataProvider $fakeDataProvider
    ) {
        $this->linkyDataProvider = $linkyDataProvider;
        $this->meteoFranceDataProvider = $meteoFranceDataProvider;
        $this->fakeDataProvider = $fakeDataProvider;

        parent::__construct($entityManager, $feedRepository, $feedDataRepository, $dataValueRepository);
    }

    public function fetchData(\Datetime $date, array $feeds, bool $force = false)
    {
        $feedDataProviderId = '';

        // Determine Feeds type
        foreach ($feeds as $feed) {
            if (!$feedDataProviderId) {
                $feedDataProviderId = $feed->getFeedDataProviderType();
            } else if ($feed->getFeedDataProviderType() !== $feedDataProviderId) {
                throw new \InvalidArgumentException("Should be an array of Feeds with the same data provider type here !");
            }
        }

        switch ($feedDataProviderId) {
            case Feed::FEED_DATA_PROVIDER_LINKY :
                $this->linkyDataProvider->fetchData($date, $feeds, $force);
                break;
            case Feed::FEED_DATA_PROVIDER_METEO_FRANCE:
                $this->meteoFranceDataProvider->fetchData($date, $feeds, $force);
                break;
            case Feed::FEED_DATA_PROVIDER_FAKE:
                $this->fakeDataProvider->fetchData($date, $feeds, $force);
                break;
            default:
                throw new \InvalidArgumentException("There's no data provider of type : " . $feedDataProviderId);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getParametersName(Feed $feed): array
    {
        switch ($feed->getFeedDataProviderType()) {
            case Feed::FEED_DATA_PROVIDER_LINKY :
                return LinkyDataProvider::getParametersName($feed);
            case Feed::FEED_DATA_PROVIDER_METEO_FRANCE:
                return MeteoFranceDataProvider::getParametersName($feed);
            case Feed::FEED_DATA_PROVIDER_FAKE:
                return [];
            default:
                throw new \InvalidArgumentException("There's no data provider for type of feed : " . $feed->getFeedType());
        }
    }
}
