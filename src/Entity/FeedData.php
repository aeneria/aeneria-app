<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedData
 *
 * @ORM\Table(name="feed_data")
 * @ORM\Entity(repositoryClass="App\Repository\FeedDataRepository")
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
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Feed")
     * @ORM\JoinColumn(nullable=false)
     */
    private $feed;

    /**
     * @var string
     *
     * @ORM\Column(name="data_type", type="string", length=150)
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dataType
     *
     * @param integer $dataType
     *
     * @return FeedData
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     *
     * @return integer
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set feed
     *
     * @param \App\Entity\Feed $feed
     *
     * @return FeedData
     */
    public function setFeed(\App\Entity\Feed $feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return \App\Entity\Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }
}
