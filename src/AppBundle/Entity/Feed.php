<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feed
 *
 * @ORM\Table(name="feed")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FeedRepository")
 */
class Feed
{
    public const FEED_TYPES = [
        'LINKY' => [
            'ID' => 1,
            'NAME' => 'Linky',
            'PARAM' => ['LOGIN', 'PASSWORD', 'ADDRESS'],
            'DATA_TYPE' => [
                'CONSO_ELEC' => [
                    'UNIT' => 'KWh',
                ]
            ],
            'FETCH_CALLBACK' => 'fetchLinkyData',
        ],
        'METEO_FRANCE' => [
            'ID' => 2,
            'NAME' => 'Meteo France',
            'PARAM' => ['STATION_ID', 'CITY_NAME'],
            'DATA_TYPE' => [
                'TEMPERATURE' => [
                    'UNIT' => '°C',
                ],
                'DJU' => [
                    'UNIT' => 'DJU',
                ],
                'PRESSURE' => [
                    'UNIT' => 'hPa',
                ],
                'HUMIDITY' => [
                    'UNIT' => '%',
                ],
                'NEBULOSITY' => [
                    'UNIT' => '%',
                ],
            ],
            'FETCH_CALLBACK' => 'fetchMeteoFranceData',
        ],
    ];

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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="feed_type", type="string", length=255, unique=true)
     */
    private $feedType;

    /**
     * @var array
     *
     * @ORM\Column(name="param", type="json_array")
     */
    private $param;

    /**
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean")
     */
    private $public;

    /**
     * @var int
     *
     * @ORM\Column(name="creator", type="integer")
     */
    private $creator;


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
     * Set name
     *
     * @param string $name
     *
     * @return Feed
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set param
     *
     * @param array $param
     *
     * @return Feed
     */
    public function setParam($param)
    {
        $this->param = $param;

        return $this;
    }

    /**
     * Get param
     *
     * @return array
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Set public
     *
     * @param boolean $public
     *
     * @return Feed
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * Get public
     *
     * @return boolean
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set creator
     *
     * @param integer $creator
     *
     * @return Feed
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return integer
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set feedType
     *
     * @param string $feedType
     *
     * @return Feed
     */
    public function setFeedType($feedType)
    {
        $this->feedType = $feedType;

        return $this;
    }

    /**
     * Get feedType
     *
     * @return string
     */
    public function getFeedType()
    {
        return $this->feedType;
    }
}
