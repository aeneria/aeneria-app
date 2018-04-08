<?php

namespace AppBundle\Object;


use AppBundle\Entity\Feed;
use AppBundle\Entity\DataValue;
use Doctrine\ORM\EntityManager;
use AppBundle\Controller\DataApiController;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Meteo France API implementation and utils
 */
class MeteoFrance {

    /**
     * @var Feed
     */
    private $feed;

    /**
     *
     * @var EntityManager
     */
    private $entityManager;

    public const SYNOP_BASE_PATH = 'https://donneespubliques.meteofrance.fr/donnees_libres/Txt/Synop/';
    public const SYNOP_DATA = 'synop.'; // exemple synop.2018040800.csv
    public const SYNOP_POSTES = 'postesSynop.csv';

    public function __construct(Feed $feed, EntityManager $entityManager)
    {
        $this->feed = $feed;
        $this->entityManager = $entityManager;
    }

    public static function getAvailableStations()
    {
        $cities = [];

        // @TODO Get stations from meteofrance

        return $cities;
    }

    public function fetchYesterdayData()
    {
        // Get yesterday datetime
        $yesterday = new DateTime();

        // Get all 3-hours interval data from yesterday
        $rawData = $this->getRawData($yesterday);

        // Get 1 value for yesterday for each type of data
        $fastenData = $this->fastenRawData($rawData);

        // Get all feedData
        $feedDataList = $this->entityManager->getRepository('AppBundle:FeedData')->findByFeed($this->feed);

        // Foreach feedData store the value for yesterday
        /** @var \AppBundle\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            $dataType = $feedData->getDataType();
            $dataValue = new DataValue();
            $dataValue->setFrequency(DataApiController::FREQUENCY['DAY']);
            $dataValue->setFeedData($feedData);
            $dataValue->setValue($fastenData[$dataType]['VALUE']);
            $dataValue->setDate($yesterday);

            // Persit the dataValue
            $this->entityManager->persist($dataValue);
        }

        // Flush all persisted DataValue
        $this->entityManager->flush();
    }

    public function refreshAgregateValue()
    {
        // Refreshing agregate value for current week
        $currentWeek = new DateTime();
        $this->refreshWeekValue($currentWeek);

        // Refreshing agregate value for current month
        $currentMonth = new DateTime();
        $this->refreshMonthValue($currentMonth);
    }

    private function getRawData(DateTime $date)
    {
        $rawData = [];

        // @TODO get datas from meteofrance

        return $rawData;
    }

    private function fastenRawData($rawDatas)
    {
        $fastenData = [];

        // @TODO calculate each data type

        return $fastenData;
    }

    private function refreshWeekValue(DateTime $date)
    {
        // @TODO
    }

    private function refreshMonthValue(DateTime $date)
    {
        // @TODO
    }
}
