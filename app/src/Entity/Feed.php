<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Feed
 */
class Feed implements \JsonSerializable
{
    public const FEED_TYPE_ELECTRICITY = 'ELECTRICITY';
    public const FEED_TYPE_GAZ = 'GAZ';
    public const FEED_TYPE_METEO = 'METEO';

    public const FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT = 'ENEDIS_DATA_CONNECT';
    public const FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT_PROXIFIED = 'ENEDIS_DATA_CONNECT_PROXIFIED';
    public const FEED_DATA_PROVIDER_GRDF_ADICT = 'GRDF_ADICT';
    public const FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED = 'GRDF_ADICT_PROXIFIED';
    public const FEED_DATA_PROVIDER_METEO_FRANCE = 'METEO_FRANCE';
    public const FEED_DATA_PROVIDER_FAKE = 'FAKE';

    private int $id;
    private string $name;
    private string $feedType;
    private string $feedDataProviderType;
    private int $fetchError = 0;
    private array $param = [];
    /** @var Collection<int, FeedData> */
    private Collection $feedDatas;
    /** @var Collection<int, Place> */
    private Collection $places;

    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->feedDatas = new ArrayCollection();
        $this->places = new ArrayCollection();
    }

    public static function getAllFeedTypes(): array
    {
        return [
            self::FEED_TYPE_ELECTRICITY => [
                'NAME' => 'Électricité',
                'DATA_TYPE' => [FeedData::FEED_DATA_CONSO_ELEC],
                'DATA_PROVIDER_TYPE' => [
                    self::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT,
                    self::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT_PROXIFIED,
                ],
                'FREQUENCIES' => DataValue::getAllFrequencies(),
            ],
            self::FEED_TYPE_GAZ => [
                'NAME' => 'Gaz',
                'DATA_TYPE' => [FeedData::FEED_DATA_CONSO_GAZ],
                'DATA_PROVIDER_TYPE' => [
                    self::FEED_DATA_PROVIDER_GRDF_ADICT,
                    self::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED,
                ],
                'FREQUENCIES' => [
                    'DAY' => DataValue::FREQUENCY_DAY,
                    'WEEK' => DataValue::FREQUENCY_WEEK,
                    'MONTH' => DataValue::FREQUENCY_MONTH,
                    'YEAR' => DataValue::FREQUENCY_YEAR,
                ],
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
                    'DAY' => DataValue::FREQUENCY_DAY,
                    'WEEK' => DataValue::FREQUENCY_WEEK,
                    'MONTH' => DataValue::FREQUENCY_MONTH,
                    'YEAR' => DataValue::FREQUENCY_YEAR,
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
            case self::FEED_DATA_PROVIDER_ENEDIS_DATA_CONNECT_PROXIFIED:
                return 'Compteur Linky';
            case self::FEED_DATA_PROVIDER_GRDF_ADICT:
            case self::FEED_DATA_PROVIDER_GRDF_ADICT_PROXIFIED:
                return 'Compteur Gazpar';
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

    public function addFetchError(): self
    {
        $this->fetchError = $this->fetchError + 1;

        return $this;
    }

    public function setFetchError(int $nbErrors): self
    {
        $this->fetchError = $nbErrors;

        return $this;
    }

    public function getFetchError(): int
    {
        return $this->fetchError;
    }

    /**
     * Est-ce que le feed courant a déjà eu trop de problème lors des dernières
     * récupérations de données
     */
    public function hasToManyFetchError(): bool
    {
        $nbFetchErrors = $this->getFetchError();

        return $nbFetchErrors > 100;
    }

    public function resetFetchError(): self
    {
        $this->fetchError = 0;

        return $this;
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

    public function getParam(): array
    {
        return $this->param;
    }

    public function getSingleParam(string $name, $default = null)
    {
        return $this->param[$name] ?? $default;
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

    public function getFrequencies(): array
    {
        return self::getFrequenciesFor($this->getFeedType());
    }

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function getFirstPlace(): ?Place
    {
        return $this->places->first();
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places->add($place);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        $this->places->removeElement($place);

        return $this;
    }

    /**
     * @param Place[] $places
     */
    public function setPlaces(array $places): self
    {
        $this->places = new ArrayCollection($places);

        return $this;
    }

    /**
     * @return Collection<FeedData>
     */
    public function getFeedDatas(): Collection
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

        return $this
            ->feedDatas
            ->findFirst(
                fn (int $key, FeedData $feedData) => $feedDataType === $feedData->getDataType()
            )
        ;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        $feedDataList = \iterator_to_array($this->getFeedDatas());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->feedType,
            'dataProvider' => $this->feedDataProviderType,
            'param' => $this->param,
            'frequencies' => $this->getFrequencies(),
            'feedDataList' => \array_map(
                function (FeedData $feedData) {
                    return $feedData->jsonSerialize();
                },
                $feedDataList
            ),
            'fetchError' => $this->fetchError,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
