<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * FeedData
 */
class FeedData implements \JsonSerializable
{
    public const FEED_DATA_CONSO_ELEC = 'CONSO_ELEC';
    public const FEED_DATA_CONSO_GAZ = 'CONSO_GAZ';
    public const FEED_DATA_TEMPERATURE = 'TEMPERATURE';
    public const FEED_DATA_TEMPERATURE_MIN = 'TEMPERATURE_MIN';
    public const FEED_DATA_TEMPERATURE_MAX = 'TEMPERATURE_MAX';
    public const FEED_DATA_DJU = 'DJU';
    public const FEED_DATA_PRESSURE = 'PRESSURE';
    public const FEED_DATA_HUMIDITY = 'HUMIDITY';
    public const FEED_DATA_NEBULOSITY = 'NEBULOSITY';
    public const FEED_DATA_RAIN = 'RAIN';

    private int $id;
    private Feed $feed;
    private string $dataType;

    /**
     * Get unit for a type of data.
     */
    public static function getUnitFor(string $feedDataType): string
    {
        switch ($feedDataType) {
            case self::FEED_DATA_CONSO_ELEC:
                return 'KWh';
            case self::FEED_DATA_CONSO_GAZ:
                return 'm3';
            case self::FEED_DATA_TEMPERATURE:
            case self::FEED_DATA_TEMPERATURE_MIN:
            case self::FEED_DATA_TEMPERATURE_MAX:
                return '°C';
            case self::FEED_DATA_DJU:
                return 'DJU';
            case self::FEED_DATA_PRESSURE:
                return 'hPa';
            case self::FEED_DATA_HUMIDITY:
            case self::FEED_DATA_NEBULOSITY:
                return '%';
            case self::FEED_DATA_RAIN:
                return 'mm';
            default:
                return '';
        }
    }

    /**
     * Get all labels for FeedDataType
     */
    public static function getAllTypeLabels(): array
    {
        return [
            self::FEED_DATA_CONSO_ELEC => "Consommation d'électricité",
            self::FEED_DATA_CONSO_GAZ => "Consommation de gaz",
            self::FEED_DATA_TEMPERATURE => "Température",
            self::FEED_DATA_TEMPERATURE_MIN => "Température minimale",
            self::FEED_DATA_TEMPERATURE_MAX => "Température maximale",
            self::FEED_DATA_DJU => 'Degrés Jour Unifié',
            self::FEED_DATA_PRESSURE => 'Pression',
            self::FEED_DATA_HUMIDITY => 'Humidité',
            self::FEED_DATA_NEBULOSITY => 'Nébulosité',
            self::FEED_DATA_RAIN => 'Précipiations',
        ];
    }

    /**
     * Get unit for a type of data.
     */
    public static function getLabelFor(string $feedDataType): string
    {
        if (!\array_key_exists($feedDataType, self::getAllTypeLabels())) {
            throw new \InvalidArgumentException(\sprintf(
                'Le type de FeedData %s n\'existe pas',
                $feedDataType
            ));
        }

        return self::getAllTypeLabels()[$feedDataType];
    }

    public function setId(int $id): FeedData
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set dataType
     */
    public function setDataType(string $dataType): self
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * Get dataType human readable
     */
    public function getDisplayDataType(): string
    {
        return self::getLabelFor($this->dataType);
    }

    /**
     * Set feed
     */
    public function setFeed(Feed $feed): self
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     */
    public function getFeed(): Feed
    {
        return $this->feed;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'label' => $this->getDisplayDataType(),
            'type' => $this->dataType,
        ];
    }
}
