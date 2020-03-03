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
class FakeDataProvider extends AbstractFeedDataProvider {

    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false)
    {
        foreach ($feeds as $feed) {
            if ($force || !$this->feedRepository->isUpToDate($feed, $date, $feed->getFrequencies())) {
                switch ($feed->getFeedType()) {
                    case Feed::FEED_TYPE_METEO :
                        $this->generateMeteoData($date, $feed);
                        break;
                    case Feed::FEED_TYPE_ELECTRICITY:
                        $this->generateElectricityData($date, $feed);
                        break;
                }
            }
        }
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

            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                $date,
                DataValue::FREQUENCY['DAY'],
                \rand($min * 10, $max * 10)/10
            );
            $this->entityManager->flush();

        }

        $this->performAgregateValue($date, $feed, DataValue::FREQUENCY['WEEK']);
        $this->performAgregateValue($date, $feed, DataValue::FREQUENCY['MONTH']);
        $this->performAgregateValue($date, $feed, DataValue::FREQUENCY['YEAR']);
    }

    /**
     * Generate Fake data for a electricty typed feed for date for all frenquencies.
     */
    private function generateElectricityData(\DateTimeImmutable $date, Feed $feed)
    {
        // Get feedData.
        $feedData = $this->feedDataRepository->findOneByFeed($feed);

        // 0 -> 5 kWh
        for ($hour = 0; $hour < 24; $hour++) {

            $this->feedDataRepository->updateOrCreateValue(
                $feedData,
                new \DateTimeImmutable($date->format("Y-m-d") . $hour . ':00'),
                DataValue::FREQUENCY['HOUR'],
                \rand(0, 50)/10
            );
        }
        $this->entityManager->flush();

        $this->performAgregateValue($date, $feed, DataValue::FREQUENCY['DAY']);
        $this->performAgregateValue($date, $feed, DataValue::FREQUENCY['WEEK']);
        $this->performAgregateValue($date, $feed, DataValue::FREQUENCY['MONTH']);
        $this->performAgregateValue($date, $feed, DataValue::FREQUENCY['YEAR']);
    }
}
