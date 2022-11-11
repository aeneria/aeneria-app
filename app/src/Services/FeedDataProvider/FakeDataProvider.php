<?php

namespace App\Services\FeedDataProvider;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;

/**
 * Fake data provider
 *
 * @warning Only use for development purpose
 * @see App\Command\Dev\GenerateFakeDataCommand
 */
class FakeDataProvider extends AbstractFeedDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getFetchStrategy(): string
    {
        return parent::FETCH_STRATEGY_ONE_BY_ONE;
    }

    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false): array
    {
        foreach ($feeds as $feed) {
            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $feed->getFrequencies())) {
                switch ($feed->getFeedType()) {
                    case Feed::FEED_TYPE_METEO:
                        $this->generateMeteoData($date, $feed);
                        break;
                    case Feed::FEED_TYPE_ELECTRICITY:
                        $this->generateElectricityData($date, $feed);
                        break;
                    case Feed::FEED_TYPE_GAZ:
                        $this->generateGazData($date, $feed);
                        break;
                }
            }
        }

        return [];
    }

    /**
     * Generate Fake data for a meteo typed feed for date for all frenquencies.
     */
    private function generateMeteoData(\DateTimeImmutable $date, Feed $feed)
    {
        $DataTypes = [
            FeedData::FEED_DATA_TEMPERATURE => [
                'min' => -10,
                'max' => 40,
            ],
            FeedData::FEED_DATA_TEMPERATURE_MIN => [
                'min' => -10,
                'max' => 40,
            ],
            FeedData::FEED_DATA_TEMPERATURE_MAX => [
                'min' => -10,
                'max' => 40,
            ],
            FeedData::FEED_DATA_DJU => [
                'min' => 0,
                'max' => 4,
            ],
            FeedData::FEED_DATA_HUMIDITY => [
                'min' => 0,
                'max' => 100,
            ],
            FeedData::FEED_DATA_NEBULOSITY => [
                'min' => 0,
                'max' => 100,
            ],
            FeedData::FEED_DATA_RAIN => [
                'min' => 0,
                'max' => 20,
            ],
            FeedData::FEED_DATA_PRESSURE => [
                'min' => 103668,
                'max' => 98167,
            ],
        ];

        foreach ($this->feedDataRepository->findByFeed($feed) as $feedData) {
            $type = $feedData->getDataType();

            $min = $DataTypes[$type]['min'];
            $max = $DataTypes[$type]['max'];

            $this->dataValueRepository->updateOrCreateValue(
                $feedData,
                $date,
                DataValue::FREQUENCY_DAY,
                \rand($min * 10, $max * 10) / 10
            );
            $this->entityManager->flush();
        }

        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_WEEK);
        $this->entityManager->flush();
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_MONTH);
        $this->entityManager->flush();
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_YEAR);
        $this->entityManager->flush();
    }

    /**
     * Generate Fake data for a electricty typed feed for date for all frenquencies.
     */
    private function generateElectricityData(\DateTimeImmutable $date, Feed $feed)
    {
        // Get feedData.
        $feedData = $this->feedDataRepository->findOneByFeed($feed);

        // 0 -> 5 kWh
        for ($hour = 0; $hour < 24; ++$hour) {
            $value = \rand(0, 50) / 10;

            // Try to generate nice value for 24*7 graph
            switch ((int) $date->format('N')) {
                case 6:
                case 7:
                    // Saturday and sunday
                    if (\in_array($hour, [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23])) {
                        $value = \rand(0, 50) / 10;
                    } else {
                        $value = \rand(0, 50) / 100;
                    }
                break;
                default:
                    // weekday
                    if (\in_array($hour, [7, 8, 9, 17, 18, 19, 20, 21, 22, 23])) {
                        $value = \rand(0, 50) / 10;
                    } else {
                        $value = \rand(0, 50) / 100;
                    }
            }

            $this->dataValueRepository->updateOrCreateValue(
                $feedData,
                new \DateTimeImmutable($date->format("Y-m-d") . $hour . ':00'),
                DataValue::FREQUENCY_HOUR,
                $value
            );
        }
        $this->entityManager->flush();

        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_DAY);
        $this->entityManager->flush();
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_WEEK);
        $this->entityManager->flush();
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_MONTH);
        $this->entityManager->flush();
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_YEAR);
        $this->entityManager->flush();
    }

    /**
     * Generate Fake data for a gaz typed feed for date for all frenquencies.
     */
    private function generateGazData(\DateTimeImmutable $date, Feed $feed)
    {
        // Get feedData.
        $feedData = $this->feedDataRepository->findOneByFeed($feed);
        switch ((int) $date->format('n')) {
            case 10:
            case 11:
            case 12:
            case 1:
            case 2:
            case 3:
                $value = \rand(0, 120);
                break;
            default:
                $value = \rand(0, 10);
        }

        $this->dataValueRepository->updateOrCreateValue(
            $feedData,
            $date,
            DataValue::FREQUENCY_DAY,
            $value
        );
        $this->entityManager->flush();

        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_WEEK);
        $this->entityManager->flush();
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_MONTH);
        $this->entityManager->flush();
        $this->dataValueRepository->updateOrCreateAgregateValue($date, $feed, DataValue::FREQUENCY_YEAR);
        $this->entityManager->flush();
    }
}
