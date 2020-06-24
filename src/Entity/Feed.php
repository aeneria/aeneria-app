<?php

namespace App\Entity;

/**
 * Feed
 */
class Feed
{
    const FEED_TYPE_ELECTRICITY = 'ELECTRICITY';
    const FEED_TYPE_METEO = 'METEO';

    const FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT = 'ENEDIS_DATA_CONNECT';
    const FEED_DATA_PROVIDER_LINKY = 'LINKY';
    const FEED_DATA_PROVIDER_METEO_FRANCE = 'METEO_FRANCE';
    const FEED_DATA_PROVIDER_FAKE = 'FAKE';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $feedType;

    /**
     * @var string
     */
    private $feedDataProviderType;

    /**
     * @var array
     */
    private $param = [];

    /**
     * @var FeedData[]
     */
    private $feedDatas;

    /**
     * @var Place
     */
    private $place;

    public static function getAllFeedTypes(): array
    {
        return [
            self::FEED_TYPE_ELECTRICITY => [
                'NAME' => 'Électricité',
                'DATA_TYPE' => [FeedData::FEED_DATA_CONSO_ELEC],
                'DATA_PROVIDER_TYPE' => [self::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT],
                'FREQUENCIES' => DataValue::getAllFrequencies(),
            ],
            self::FEED_TYPE_METEO => [
                'NAME' => 'Météo',
                'DATA_TYPE' => [
                    FeedData::FEED_DATA_TEMPERATURE,
                    FeedData::FEED_DATA_TEMPERATURE_MIN,
                    FeedData::FEED_DATA_TEMPERATURE_MAX,
                    FeedData::FEED_DATA_DJU,
                    FeedData::FEED_DATA_PRESSURE,
                    FeedData::FEED_DATA_HUMIDITY,
                    FeedData::FEED_DATA_NEBULOSITY,
                    FeedData::FEED_DATA_RAIN,
                ],
                'DATA_PROVIDER_TYPE' => [self::FEED_DATA_PROVIDER_METEO_FRANCE],
                'FREQUENCIES' => [
                    DataValue::FREQUENCY_DAY,
                    DataValue::FREQUENCY_WEEK,
                    DataValue::FREQUENCY_MONTH,
                ],
            ],
        ];
    }

    public static function getAllowedDataProvidersFor(string $feedType): array
    {
        if (\key_exists($feedType, self::getAllFeedTypes())) {
            return self::getAllFeedTypes()[$feedType]['DATA_PROVIDER_TYPE'];
        } else {
            throw new \InvalidArgumentException("Feed type " . $feedType . " does not exist !");
        }
    }

    public static function getNameFor(string $feedType): string
    {
        if (\key_exists($feedType, self::getAllFeedTypes())) {
            return self::getAllFeedTypes()[$feedType]['NAME'];
        } else {
            throw new \InvalidArgumentException("Feed type " . $feedType . " does not exist !");
        }
    }

    public static function getFeedDataProviderNameFor(string $feedDataProviderType): ?string
    {
        switch ($feedDataProviderType) {
            case self::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT:
                case self::FEED_DATA_PROVIDER_LINKY:
                return 'Compteur Linky';
            case self::FEED_DATA_PROVIDER_METEO_FRANCE:
                return 'Météo France';
            case self::FEED_DATA_PROVIDER_FAKE:
                return 'Fake data provider';
            default:
                return null;
        }
    }

    public static function getFrequenciesFor(string $feedType): array
    {
        if (\key_exists($feedType, self::getAllFeedTypes())) {
            return self::getAllFeedTypes()[$feedType]['FREQUENCIES'];
        } else {
            throw new \InvalidArgumentException("Feed type " . $feedType . " does not exist !");
        }
    }

    public static function getDataTypeFor(string $feedType): array
    {
        if (\key_exists($feedType, self::getAllFeedTypes())) {
            return self::getAllFeedTypes()[$feedType]['DATA_TYPE'];
        } else {
            throw new \InvalidArgumentException("Feed type " . $feedType . " does not exist !");
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setParam(array $param): self
    {
        $this->param = $param;

        return $this;
    }

    public function setSingleParam(string $name, $value): self
    {
        $this->param[$name] = $value;

        return $this;
    }

    /**
     * Get param
     *
     * @return array
     */
    public function getParam(): array
    {
        return $this->param ?? [];
    }

    public function setFeedType(string $feedType): self
    {
        $this->feedType = $feedType;

        return $this;
    }

    public function getFeedType(): string
    {
        return $this->feedType;
    }

    public function setFeedDataProviderType(string $feedDataProviderType): self
    {
        $this->feedDataProviderType = $feedDataProviderType;

        return $this;
    }

    public function getFeedDataProviderType(): string
    {
        return $this->feedDataProviderType;
    }

    public function getFeedDataProviderTypeName(): ?string
    {
        return self::getFeedDataProviderNameFor($this->feedDataProviderType);
    }

    public function setPlace(Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function getFrequencies(): array
    {
        return self::getFrequenciesFor($this->getFeedType());
    }

    /**
     * @return FeedData[]
     */
    public function getFeedDatas(): ?iterable
    {
        return $this->feedDatas;
    }

    public function getFeedData(string $feedDataType): ?FeedData
    {
        if (!\array_key_exists($feedDataType, FeedData::getAllTypeLabels())) {
            throw new \InvalidArgumentException(\sprintf(
                'Le type de FeedData %s n\'existe pas',
                $feedDataType
            ));
        }

        if ($this->feedDatas) {
            foreach ($this->feedDatas as $feedData) {
                if ($feedDataType === $feedData->getDataType()) {
                    return $feedData;
                }
            }
        }

        return null;
    }
}
