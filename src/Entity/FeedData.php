<?php

namespace App\Entity;

/**
 * FeedData
 */
class FeedData
{
    const FEED_DATA_CONSO_ELEC = 'CONSO_ELEC';
    const FEED_DATA_TEMPERATURE = 'TEMPERATURE';
    const FEED_DATA_TEMPERATURE_MIN = 'TEMPERATURE_MIN';
    const FEED_DATA_TEMPERATURE_MAX = 'TEMPERATURE_MAX';
    const FEED_DATA_DJU = 'DJU';
    const FEED_DATA_PRESSURE = 'PRESSURE';
    const FEED_DATA_HUMIDITY = 'HUMIDITY';
    const FEED_DATA_NEBULOSITY = 'NEBULOSITY';
    const FEED_DATA_RAIN = 'RAIN';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Feed
     */
    private $feed;

    /**
     * @var string
     */
    private $dataType;

    /**
     * Get unit for a type of data.
     */
    public static function getUnitFor(string $feedDataType): string
    {
        switch ($feedDataType) {
            case self::FEED_DATA_CONSO_ELEC:
                return 'KWh';
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
     * Get unit for a type of data.
     */
    public static function getLabelFor(string $feedDataType): string
    {
        switch ($feedDataType) {
            case self::FEED_DATA_CONSO_ELEC:
                return "Consommation d'électricité";
            case self::FEED_DATA_TEMPERATURE:
                return "Température";
            case self::FEED_DATA_TEMPERATURE_MIN:
                return "Température minimale";
            case self::FEED_DATA_TEMPERATURE_MAX:
                return "Température maximale";
            case self::FEED_DATA_DJU:
                return 'Degrés Jour Unifié';
            case self::FEED_DATA_PRESSURE:
                return 'Pression';
            case self::FEED_DATA_HUMIDITY:
                return 'Humidité';
            case self::FEED_DATA_NEBULOSITY:
                return 'Nébulosité';
            case self::FEED_DATA_RAIN:
                return 'Précipiations';
            default:
                return '';
        }
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
}
