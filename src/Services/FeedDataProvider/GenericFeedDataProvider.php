<?php
namespace App\Services\FeedDataProvider;

use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;

class GenericFeedDataProvider extends AbstractFeedDataProvider {

    private $linkyDataProvider;
    private $meteoFranceDataProvider;

    public function __construct(EntityManagerInterface $entityManager, FeedRepository $feedRepository, FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository, LinkyDataProvider $linkyDataProvider, MeteoFranceDataProvider $meteoFranceDataProvider)
    {
        $this->linkyDataProvider = $linkyDataProvider;
        $this->meteoFranceDataProvider = $meteoFranceDataProvider;

        parent::__construct($entityManager, $feedRepository, $feedDataRepository, $dataValueRepository);
    }

    public function fetchData(\Datetime $date, array $feeds, bool $force = false)
    {
        $feedType = '';

        // Determine Feeds type
        foreach ($feeds as $feed) {
            if (!$feedType) {
                $feedType = $feed->getFeedType();
            } else if ($feed->getFeedType() !== $feedType) {
                throw new \InvalidArgumentException("Should be an array of of Feeds with the same type here !");
            }
        }

        switch ($feedType) {
            case 'LINKY' :
                $this->linkyDataProvider->fetchData($date, $feeds, $force);
                break;
            case 'METEO_FRANCE':
                $this->meteoFranceDataProvider->fetchData($date, $feeds, $force);
                break;
            default:
                throw new \InvalidArgumentException("There's no data provider for type of feed : " . $feedType);
        }
    }
}
