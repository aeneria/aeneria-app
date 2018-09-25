<?php

namespace AppBundle\Object;


use AppBundle\Entity\Feed;
use AppBundle\Entity\DataValue;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;

/**
 * Meteo France API to get SYNOP Observations.
 */
class MeteoFrance {

    /**
     * Different usefull URIs.
     */
    const SYNOP_BASE_PATH = 'https://donneespubliques.meteofrance.fr/';
    const SYNOP_DATA = 'donnees_libres/Txt/Synop/synop.'; // exemple synop.2018040800.csv
    const SYNOP_POSTES = 'donnees_libres/Txt/Synop/postesSynop.csv';

    /**
     * Reference Temperature for DJU calculation.
     */
    const BASE_DJU = 18;

    /**
     * Convert degree Kelvin to Celsius.
     */
    const KELVIN_TO_CELSIUS = 273.15;

    /**
     * Correspondance between synop variable name and our variable name.
     */
    const SYNOP_DATA_NAME = [
        'STATION_ID' => 'numer_sta',
        'TEMPERATURE' => 't',
        'PRESSURE' => 'pres',
        'HUMIDITY' => 'u',
        'NEBULOSITY' => 'n',
        'RAIN' => 'rr3',
        'DJU' => '',
    ];

    /**
     * Frequencies for MeteoFrance FeedData.
     * @var array
     */
    const FREQUENCY = [
        DataValue::FREQUENCY['DAY'],
        DataValue::FREQUENCY['WEEK'],
        DataValue::FREQUENCY['MONTH'],
        DataValue::FREQUENCY['YEAR'],
    ];

    /**
     * Feed correspondingto the MeteoFrance Object
     * @var Feed
     */
    private $feed;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Constructor.
     *
     * @param Feed $feed
     * @param EntityManager $entityManager
     */
    public function __construct(Feed $feed, EntityManager $entityManager)
    {
        $this->feed = $feed;
        $this->entityManager = $entityManager;
    }

    /**
     * Get all available station on MeteoFrance for SYNOP observation.
     * @return array
     */
    public static function getAvailableStations()
    {
        $stations = [];

        // Declare the http client.
        $client = new Client(['base_uri' => self::SYNOP_BASE_PATH]);
        $clientOption = [
            'verify' => FALSE,
            'stream' => TRUE,
        ];

        // We get the raw CSV.
        $response = $client->get(self::SYNOP_POSTES, $clientOption);
        $stationsData = $response->getBody()->getContents();
        if ($response->getStatusCode() == 200) {
            // We parse it get a nice table.
            $rows = array_filter(preg_split('/\R/', $stationsData));
            $header = NULL;

            foreach($rows as $row) {
                $row = str_getcsv ($row, ';');

                if(!$header) {
                    $header = $row;
                }
                else {
                    $row = array_combine($header, $row);

                    // We only keep ID and name for each station.
                    $stations[ucwords(strtolower($row['Nom']))] = (int)$row['ID'];
                }
            }

            // Sort stations.
            ksort($stations,SORT_STRING);
        }

        return $stations;
    }

    /**
     * Fetch SYNOP data for yesterday and persist its in database.
     *
     * @param \DateTime $date
     */
    public function fetchData(\DateTime $date)
    {
        // Get datetime.
        $date = new \DateTime($date->format("Y-m-d 00:00:00"));

        // Get all 3-hours interval data from yesterday.
        $rawData = $this->getRawData($date);

        // Get 1 value for yesterday for each type of data.
        $fastenData = $this->fastenRawData($rawData);

        // Get all feedData.
        $feedDataList = $this->entityManager->getRepository('AppBundle:FeedData')->findByFeed($this->feed);

        // Foreach feedData store the value for yesterday.
        /** @var \AppBundle\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            $dataType = $feedData->getDataType();
            $feedData->updateOrCreateValue(
                $date,
                DataValue::FREQUENCY['DAY'],
                $fastenData[$dataType],
                $this->entityManager
            );
        }

        // Flush all persisted DataValue.
        $this->entityManager->flush();

        // Refresh week and month aggregate data.
        $this->refreshAgregateValue($date);
    }

    /**
     * Create or refresh month and week agregate data for the date.
     * Persist it in EntityManager.
     *
     * @param \DateTime $date
     */
    public function refreshAgregateValue(\DateTime $date)
    {
        // Refreshing agregate value for current week.
        $this->refreshWeekValue($date);

        // Refreshing agregate value for current month.
        $this->refreshMonthValue($date);

        // Flush all persisted DataValue.
        $this->entityManager->flush();
    }

    /**
     * Get each 3-hours raw SYNOP data for the date from Meteo France.
     *
     * @param \DateTime $date
     * @return array[]
     */
    private function getRawData(\DateTime $date)
    {
        $rawData = [];

        // Declare the http client.
        $client = new Client(['base_uri' => self::SYNOP_BASE_PATH]);
        $clientOption = [
            'verify' => false,
            'stream' => true,
        ];

        $dateFormated = $date->format('Ymd');

        // We get data foreach 3 hours intervall from 00 to 21h.
        for ($hour = 0; $hour < 24; $hour += 3) {
            // We build the path to the date's file (ex: synop.2018040800.csv).
            $uri= self::SYNOP_DATA . $dateFormated . sprintf("%02d", $hour) . '.csv';

            // We get the raw CSV.
            $response = $client->get($uri, $clientOption);
            $synopData = $response->getBody()->getContents();
            if ($response->getStatusCode() == 200) {
                // We parse it to only get what we need.
                $rawData[] = $this->getStationRawData($synopData);
            }
        }

        return $rawData;
    }

    /**
     * Extract data for the day from all 3-hours SYNOP for a day.
     * (temperature, humidity, nebulosity, pressure & DJU)
     *
     * @param array $rawData
     * @return float[]
     */
    private function fastenRawData($rawData)
    {
        $fastenData = [
            'TEMPERATURE' => 0,
            'DJU' => 0,
            'HUMIDITY' => 0,
            'NEBULOSITY' => 0,
            'PRESSURE' => 0,
            'RAIN' => 0,
        ];

        $nbNebulosity = 0;
        $nbHumidity = 0;
        $nbPressure = 0;
        $nbTemperature = 0;
        $nbRain = 0;

        $tempMin = NULL;
        $tempMax = NULL;

        foreach ($rawData as $hourData) {
            if (isset($hourData[self::SYNOP_DATA_NAME['NEBULOSITY']])){
                $fastenData['NEBULOSITY'] += $hourData[self::SYNOP_DATA_NAME['NEBULOSITY']];
                $nbNebulosity++;
            }
            if (isset($hourData[self::SYNOP_DATA_NAME['PRESSURE']])){
                $fastenData['PRESSURE'] += $hourData[self::SYNOP_DATA_NAME['PRESSURE']];
                $nbPressure++;
            }
            if (isset($hourData[self::SYNOP_DATA_NAME['HUMIDITY']])){
                $fastenData['HUMIDITY'] += $hourData[self::SYNOP_DATA_NAME['HUMIDITY']];
                $nbHumidity++;
            }
            if (isset($hourData[self::SYNOP_DATA_NAME['RAIN']])){
                if ($hourData[self::SYNOP_DATA_NAME['RAIN']] >= 0) {
                    $fastenData['RAIN'] += $hourData[self::SYNOP_DATA_NAME['RAIN']];
                }
                $nbRain++;
            }
            if (isset($hourData[self::SYNOP_DATA_NAME['TEMPERATURE']])) {
                $curTemperature = $hourData[self::SYNOP_DATA_NAME['TEMPERATURE']] - self::KELVIN_TO_CELSIUS;
                $fastenData['TEMPERATURE'] += $curTemperature;
                $nbTemperature++;
                $this->updateExtrema($tempMin, $tempMax, $curTemperature);
            }
        }

        // Calculate averageof each value.
        if ($nbTemperature > 0) {
            $fastenData['TEMPERATURE'] = round($fastenData['TEMPERATURE'] / $nbTemperature, 1);
        }
        if ($nbHumidity > 0) {
            $fastenData['HUMIDITY'] = round($fastenData['HUMIDITY'] / $nbHumidity, 1);
        }
        if ($nbNebulosity > 0) {
            $fastenData['NEBULOSITY'] = round($fastenData['NEBULOSITY'] / $nbNebulosity, 1);
        }
        if ($nbPressure > 0) {
            $fastenData['PRESSURE'] = round($fastenData['PRESSURE'] / $nbPressure, 1);
        }
        if ($nbRain > 0) {
            $fastenData['RAIN'] = round($fastenData['RAIN'], 1);
        }

        // Calculate DJU with temperature max and min of the day.
        if (isset($tempMin) && isset($tempMax)) {
            $fastenData['DJU'] = round($this->calculateDju($tempMin, $tempMax, 1));
        }

        return $fastenData;
    }


    /**
     * Create or refresh week agregate data for the date.
     * Persist it in EntityManager
     *
     * @param \DateTime $date
     */
    private function refreshWeekValue(\DateTime $date)
    {
        $firstDayOfWeek = clone $date;
        $w = $date->format('w') == 0 ? 6 : $date->format('w') - 1;
        $firstDayOfWeek->sub(new \DateInterval('P' . $w . 'D'));

        $lastDayOfWeek = clone $firstDayOfWeek;
        $lastDayOfWeek->add(new \DateInterval('P6D'));

        $this->performAgregateValue($firstDayOfWeek, $lastDayOfWeek, DataValue::FREQUENCY['WEEK']);
    }

    /**
     * Create or refresh month agregate data for the date.
     * Persist it in EntityManager
     *
     * @param \DateTime $date
     */
    private function refreshMonthValue(\DateTime $date)
    {
        $firstDayOfMonth = clone $date;
        $firstDayOfMonth->sub(new \DateInterval('P' . ($date->format('d') - 1) . 'D'));

        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->add(new \DateInterval('P' . ($date->format('t')) . 'D'));

        $this->performAgregateValue($firstDayOfMonth, $lastDayOfMonth, DataValue::FREQUENCY['MONTH']);
    }

    /**
     * Agregate Values between 2 date and push it to EntityManager.
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param int $frequency
     */
    private function performAgregateValue(\DateTime $startDate, \DateTime $endDate, $frequency)
    {
        // Get all feedData.
        $feedDataList = $this->entityManager->getRepository('AppBundle:FeedData')->findByFeed($this->feed);

        /** @var \AppBundle\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            if (!in_array($feedData->getDataType(), ['DJU', 'RAIN'])) {
                $agregateData = $this
                    ->entityManager
                    ->getRepository('AppBundle:DataValue')
                    ->getAverageValue(
                        $startDate,
                        $endDate,
                        $feedData,
                        DataValue::FREQUENCY['DAY']
                    )
                ;
            }
            else {
                $agregateData = $this
                    ->entityManager
                    ->getRepository('AppBundle:DataValue')
                    ->getSumValue(
                        $startDate,
                        $endDate,
                        $feedData,
                        DataValue::FREQUENCY['DAY']
                    )
                ;
            }

            if (isset($agregateData[0]['value'])) {
                $feedData->updateOrCreateValue(
                    $startDate,
                    $frequency,
                    round($agregateData[0]['value'], 1),
                    $this->entityManager
                );
            }
        }
    }

    /**
     * Extract SYNOP data for the feed's station from Meteo France CSV.
     *
     * @param string $synopData
     * @return array
     */
    private function getStationRawData($synopData)
    {
        $stationId = $this->feed->getParam()['STATION_ID'];
        $rows = array_filter(preg_split('/\R/', $synopData));
        $header = NULL;
        $data = [];

        foreach($rows as $row) {
            $row = str_getcsv ($row, ';');

            if(!$header) {
                $header = $row;
            }
            else {
                $row = array_combine($header, $row);
                if ($row[self::SYNOP_DATA_NAME['STATION_ID']] == $stationId) {
                    $data = $row;
                }
            }
        }

        return $data;
    }

    /**
     * Update min & max value.
     *
     * @param float $curMin
     * @param float $curMax
     * @param float $newValue
     */
    private function updateExtrema(&$curMin, &$curMax, $newValue)
    {
        if (!isset($curMin)) {
            $curMin = $newValue;
        }
        else {
            $curMin = min([
                $curMin,
                $newValue,
            ]);
        }
        if (!isset($curMax)) {
            $curMax = $newValue;
        }
        else {
            $curMax = max([
                $curMax,
                $newValue,
            ]);
        }
    }

    /**
     * Calculates DJU as COSTIC does.
     *
     * @see https://fr.wikipedia.org/wiki/Degr%C3%A9_jour_unifi%C3%A9
     *
     * @param float $tempMin
     * @param float $tempMax
     * @return float DJU
     */
    private function calculateDju($tempMin, $tempMax) {
        $tempAvg = ($tempMax + $tempMin)/2;
        if (self::BASE_DJU > $tempMax) {
          return self::BASE_DJU - $tempAvg;
        }
        elseif (self::BASE_DJU <= $tempMin) {
          return 0;
        }
        else {
          return (self::BASE_DJU - $tempMin) * (0.08 + 0.42 * (self::BASE_DJU - $tempMin) / ($tempMax - $tempMin));
        }
    }
}
