<?php

declare(strict_types=1);

namespace App\Services\FeedDataProvider;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Model\FetchingError;
use App\Model\StationSynop;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Meteo France API to get SYNOP Observations.
 */
class MeteoFranceDataProvider extends AbstractFeedDataProvider
{
    /**
     * Different usefull URIs.
     */
    public const SYNOP_BASE_PATH = 'https://donneespubliques.meteofrance.fr/';
    public const SYNOP_DATA = 'donnees_libres/Txt/Synop/synop.'; // exemple synop.2018040800.csv
    public const SYNOP_POSTES = '/postesSynop.csv';

    /**
     * Reference Temperature for DJU calculation.
     */
    public const BASE_DJU = 18;

    /**
     * Convert degree Kelvin to Celsius.
     */
    public const KELVIN_TO_CELSIUS = 273.15;

    /**
     * Correspondance between synop variable name and our variable name.
     */
    public const SYNOP_DATA_NAME = [
        'STATION_ID' => 'numer_sta',
        'TEMPERATURE' => 't',
        'PRESSURE' => 'pres',
        'HUMIDITY' => 'u',
        'NEBULOSITY' => 'n',
        'RAIN' => 'rr3',
        'DJU' => '',
    ];

    /** @var string */
    private $projectDir;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var StationSynop[] */
    private $availableStations = [];

    public function __construct(
        string $projectDir,
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        NotificationService $notificationService,
        HttpClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $entityManager,
            $feedRepository,
            $feedDataRepository,
            $dataValueRepository,
            $notificationService,
            $logger
        );

        $this->projectDir = $projectDir;
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public static function getParametersName(Feed $feed): array
    {
        return [
            'STATION_ID' => 'Id de la station',
            'CITY_NAME' => 'Ville',
         ];
    }

    /**
     * Get all available station on MeteoFrance for SYNOP observation.
     *
     * @return StationSynop[]
     */
    public function getAvailableStations(): array
    {
        if ($this->availableStations) {
            return $this->availableStations;
        }

        $stations = [];
        $header = [];

        // Reads csv files containing stations info.
        $stationsData = \file(\sprintf("%s/public%s", $this->projectDir, self::SYNOP_POSTES));

        foreach ($stationsData as $row) {
            $row = \str_getcsv($row, ';');

            if (0 == \count($header)) {
                $header = $row;
            } else {
                $row = \array_combine($header, $row);

                // We only keep ID and name for each station.
                $stations[] = new StationSynop(
                    (int) $row['ID'],
                    \ucwords(\strtolower($row['Nom'])),
                    $row['Latitude'],
                    $row['Longitude'],
                    $row['Altitude']
                );
            }
        }

        // Sort stations.
        \usort($stations, function (StationSynop $stationA, StationSynop $stationB) {
            return $stationA->label >= $stationB->label ? 1 : -1;
        });

        return $this->availableStations = $stations;
    }

    public function findStationByKey(int $key): ?StationSynop
    {
        foreach ($this->getAvailableStations() as $station) {
            if ($station->key === $key) {
                return $station;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFetchStrategy(): string
    {
        return parent::FETCH_STRATEGY_GROUPED;
    }

    /**
     * {@inheritdoc}
     */
    public static function isAvailableDataDate(\DateTimeImmutable $date): bool
    {
        // Get yesterday datetime.
        $reference = new \DateTimeImmutable();
        $reference = $reference->sub(new \DateInterval('P1D'));

        return $reference->format("Y-m-d") >= $date->format("Y-m-d");
    }

    /**
     * {@inheritdoc}
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false): array
    {
        try {
            $synopData = $this->fetchSynopData($date);
        } catch (\Exception $e) {
            return [new FetchingError(\reset($feeds), $date, $e)];
        }

        foreach ($feeds as $feed) {
            if ((!$feed instanceof Feed) || Feed::FEED_DATA_PROVIDER_METEO_FRANCE !== $feed->getFeedDataProviderType()) {
                throw new \InvalidArgumentException("Should be an array of MeteoFrance Feeds overhere !");
            }

            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $feed->getFrequencies())) {
                $this->refreshFeedData($date, $feed, $synopData);
            }
        }

        return [];
    }

    private function fetchSynopData(\DateTimeImmutable $date): array
    {
        $this->logger->debug("MeteoFrance - Start fetching synop data", ['date' => $date->format('Y-m-d')]);

        $synopData = [];

        $clientOption = [
            'verify_host' => false,
            'buffer' => true,
        ];

        $dateFormated = $date->format('Ymd');

        // We get data foreach 3 hours intervall from 00 to 21h.
        for ($hour = 0; $hour < 24; $hour += 3) {
            // We build the path to the date's file (ex: synop.2018040800.csv).
            $uri = \sprintf("%s%s%s%02d%s", self::SYNOP_BASE_PATH, self::SYNOP_DATA, $dateFormated, $hour, '.csv');

            // We get the raw CSV.
            $response = $this->httpClient->request('GET', $uri, $clientOption);
            if (200 == $response->getStatusCode()) {
                $partialSynopData = $response->getContent();

                // We parse it.
                $rows = \array_filter(\preg_split('/\R/', $partialSynopData));
                $header = [];

                foreach ($rows as $row) {
                    $row = \str_getcsv($row, ';');
                    if (0 == \count($header)) {
                        // If the first line doesn't start with the right data, then we have an error in the response
                        if ($row[0] !== self::SYNOP_DATA_NAME['STATION_ID']) {
                            break;
                        }
                        $header = $row;
                    } else {
                        $row = \array_combine($header, $row);
                        $synopData[\intval($row[self::SYNOP_DATA_NAME['STATION_ID']])][$hour] = $row;
                    }
                }
            } else {
                $this->logger->error("MeteoFrance - Synop data not found", ['date' => $date->format('Y-m-d')]);
                throw new \Exception(\sprintf('SYNOP data not found'));
            }
        }

        $this->logger->info("MeteoFrance - Synop data fetched", ['date' => $date->format('Y-m-d')]);

        return $synopData;
    }

    private function refreshFeedData(\DateTimeImmutable $date, Feed $feed, array $synopData)
    {
        $this->logger->debug("MeteoFrance - Start refreshing data", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);

        $stationId = $feed->getParam()['STATION_ID'];

        // If we have data.
        if (\key_exists($stationId, $synopData) && $rawData = $synopData[$stationId]) {
            // Get 1 value for yesterday for each type of data.
            $fastenData = $this->fastenRawData($rawData);

            // Get all feedData.
            $feedDataList = $this->feedDataRepository->findByFeed($feed);

            if (!$feedDataList) {
                throw new \Doctrine\ORM\EntityNotFoundException(\sprintf(
                    "Could not find feedDatas for feed %s.",
                    $feed->getId()
                ));
            }

            // Foreach feedData store the value for yesterday.
            foreach ($feedDataList as $feedData) {
                $dataType = $feedData->getDataType();
                if (null !== $fastenData[$dataType]) {
                    $this->dataValueRepository->updateOrCreateValue(
                        $feedData,
                        $date,
                        DataValue::FREQUENCY_DAY,
                        (string) $fastenData[$dataType]
                    );
                }
            }

            // Flush all persisted DataValue.
            $this->entityManager->flush();

            $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_WEEK);
            $this->entityManager->flush();
            $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_MONTH);
            $this->entityManager->flush();
            $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_YEAR);
            $this->entityManager->flush();

            $this->logger->info("MeteoFrance - Data refreshed", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);
        } else {
            $this->logger->info("MeteoFrance - No data to refresh", ['feed' => $feed->getId(), 'date' => $date->format('Y-m-d')]);
        }
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
            'TEMPERATURE' => null,
            'TEMPERATURE_MAX' => null,
            'TEMPERATURE_MIN' => null,
            'DJU' => null,
            'HUMIDITY' => null,
            'NEBULOSITY' => null,
            'PRESSURE' => null,
            'RAIN' => null,
        ];

        $nbNebulosity = 0;
        $nbHumidity = 0;
        $nbPressure = 0;
        $nbTemperature = 0;
        $nbRain = 0;

        foreach ($rawData as $hourData) {
            if (isset($hourData[self::SYNOP_DATA_NAME['NEBULOSITY']])
                && \is_numeric($hourData[self::SYNOP_DATA_NAME['NEBULOSITY']])) {
                $fastenData['NEBULOSITY'] += $hourData[self::SYNOP_DATA_NAME['NEBULOSITY']];
                ++$nbNebulosity;
            }
            if (isset($hourData[self::SYNOP_DATA_NAME['PRESSURE']])
            && \is_numeric($hourData[self::SYNOP_DATA_NAME['PRESSURE']])) {
                $fastenData['PRESSURE'] += $hourData[self::SYNOP_DATA_NAME['PRESSURE']];
                ++$nbPressure;
            }
            if (isset($hourData[self::SYNOP_DATA_NAME['HUMIDITY']])
            && \is_numeric($hourData[self::SYNOP_DATA_NAME['HUMIDITY']])) {
                $fastenData['HUMIDITY'] += $hourData[self::SYNOP_DATA_NAME['HUMIDITY']];
                ++$nbHumidity;
            }
            if (isset($hourData[self::SYNOP_DATA_NAME['RAIN']])
            && \is_numeric($hourData[self::SYNOP_DATA_NAME['RAIN']])) {
                if ($hourData[self::SYNOP_DATA_NAME['RAIN']] >= 0) {
                    $fastenData['RAIN'] += $hourData[self::SYNOP_DATA_NAME['RAIN']];
                }
                ++$nbRain;
            }
            if (isset($hourData[self::SYNOP_DATA_NAME['TEMPERATURE']])
            && \is_numeric($hourData[self::SYNOP_DATA_NAME['TEMPERATURE']])) {
                // Temp avg.
                $curTemp = $hourData[self::SYNOP_DATA_NAME['TEMPERATURE']] - self::KELVIN_TO_CELSIUS;
                $fastenData['TEMPERATURE'] += $curTemp;
                ++$nbTemperature;

                // Temp max.
                if (empty($fastenData['TEMPERATURE_MAX'])) {
                    $fastenData['TEMPERATURE_MAX'] = $curTemp;
                }

                $fastenData['TEMPERATURE_MAX'] = \max($fastenData['TEMPERATURE_MAX'], $curTemp);

                // Temp min.
                if (empty($fastenData['TEMPERATURE_MIN'])) {
                    $fastenData['TEMPERATURE_MIN'] = $curTemp;
                }

                $fastenData['TEMPERATURE_MIN'] = \min($fastenData['TEMPERATURE_MIN'], $curTemp);
            }
        }

        // Calculate averageof each value.
        if ($nbTemperature > 0) {
            $fastenData['TEMPERATURE'] = \round($fastenData['TEMPERATURE'] / $nbTemperature, 1);
        }
        if ($nbHumidity > 0) {
            $fastenData['HUMIDITY'] = \round($fastenData['HUMIDITY'] / $nbHumidity, 1);
        }
        if ($nbNebulosity > 0) {
            $fastenData['NEBULOSITY'] = \round($fastenData['NEBULOSITY'] / $nbNebulosity, 1);
        }
        if ($nbPressure > 0) {
            $fastenData['PRESSURE'] = \round($fastenData['PRESSURE'] / $nbPressure, 1);
        }
        if ($nbRain > 0) {
            $fastenData['RAIN'] = \round($fastenData['RAIN'], 1);
        }

        // Calculate DJU with temperature max and min of the day.
        if (!empty($fastenData['TEMPERATURE_MAX']) && !empty($fastenData['TEMPERATURE_MIN'])) {
            $fastenData['DJU'] = \round($this->calculateDju($fastenData['TEMPERATURE_MIN'], $fastenData['TEMPERATURE_MAX']));
        }

        return $fastenData;
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
    public static function calculateDju($tempMin, $tempMax)
    {
        $tempAvg = ($tempMax + $tempMin) / 2;
        if (self::BASE_DJU > $tempMax) {
            return self::BASE_DJU - $tempAvg;
        } elseif (self::BASE_DJU <= $tempMin) {
            return 0;
        } else {
            // If $tempMin == $tempMax then we have 0.42 * infinite ≈ 0
            $extra = $tempMax !== $tempMin ? 0.42 * (self::BASE_DJU - $tempMin) / ($tempMax - $tempMin) : 0;

            return (self::BASE_DJU - $tempMin) * (0.08 + $extra);
        }
    }
}
