<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feed
 *
 * @ORM\Table(name="feed")
 * @ORM\Entity(repositoryClass="App\Repository\FeedRepository")
 */
class Feed
{
    const FEED_TYPE_ELECTRICITY = 'ELECTRICITY';
    const FEED_TYPE_METEO = 'METEO';

    const FEED_DATA_PROVIDER_LINKY = 'LINKY';
    const FEED_DATA_PROVIDER_METEO_FRANCE = 'METEO_FRANCE';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=150)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="feed_type", type="string", length=150)
     */
    private $feedType;

    /**
     * @var string
     *
     * @ORM\Column(name="feed_data_provider_type", type="string", length=150)
     */
    private $feedDataProviderType;

    /**
     * @var array
     *
     * @ORM\Column(name="param", type="json_array")
     */
    private $param;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="feeds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $place;

    public static function getAllFeedTypes(): array
    {
        return [
            self::FEED_TYPE_ELECTRICITY => [
                'NAME' => 'Electricity',
                'DATA_TYPE' => [FeedData::FEED_DATA_CONSO_ELEC],
                'DATA_PROVIDER_TYPE' => [self::FEED_DATA_PROVIDER_LINKY],
            ],
            self::FEED_TYPE_METEO => [
                'NAME' => 'Meteo',
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
            ],
        ];
    }

    public static function getAllowedDataProvidersFor(string $feedType): array
    {
        if (\key_exists($feedType, self::getAllFeedTypes())) {
            return self::getAllFeedTypes()[$feedType]['DATA_PROVIDER_TYPE'];
        }
    }

    public static function getNameFor(string $feedType): array
    {
        if (\key_exists($feedType, self::getAllFeedTypes())) {
            return self::getAllFeedTypes()[$feedType]['NAME'];
        }
    }

    public static function getDataTypeFor(string $feedType): array
    {
        if (\key_exists($feedType, self::getAllFeedTypes())) {
            return self::getAllFeedTypes()[$feedType]['DATA_TYPE'];
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Feed
    {
        $this->id = $id;

        return $this;
    }

    public function setName(string $name): Feed
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setParam(array $param): Feed
    {
        $this->param = $param;

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

    public function setPublic(bool $public): Feed
    {
        $this->public = $public;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setCreator(string $creator): Feed
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCreator(): int
    {
        return $this->creator;
    }

    public function setFeedType(string $feedType): Feed
    {
        $this->feedType = $feedType;

        return $this;
    }

    public function getFeedType(): string
    {
        return $this->feedType;
    }

    public function setFeedDataProviderType(string $feedDataProviderType): Feed
    {
        $this->feedDataProviderType = $feedDataProviderType;

        return $this;
    }

    public function getFeedDataProviderType(): string
    {
        return $this->feedDataProviderType;
    }

    public function setPlace(Place $place): Feed
    {
        $this->place = $place;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }
}
