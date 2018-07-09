<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;

/**
 * Feed
 *
 * @ORM\Table(name="feed")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FeedRepository")
 */
class Feed
{
    const FEED_TYPES = [
        'LINKY' => [
            'ID' => 1,
            'NAME' => 'Linky',
            'PARAM' => [
                'ADDRESS' => 'Adresse du compteur',
                'LOGIN' => 'Adresse email du compte Enedis',
                'PASSWORD' => 'Mot de passe',
            ],
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
            'PARAM' => [
                'STATION_ID' => 'Id de la station',
                'CITY_NAME' => 'Ville',
            ],
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
                'RAIN' => [
                    'UNIT' => 'mm',
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

    /**
     * Check if there's data in DB for $date forall $feed's feedData and for all $frequencies.
     * @param EntityManager $entityManager
     * @param \DateTime $date
     * @param $frequencies array of int from DataValue frequencies
     */
    public function isUpToDate(EntityManager $entityManager, \DateTime $date, array $frequencies)
    {
        // Get all feedData.
        $feedDataList = $entityManager->getRepository('AppBundle:FeedData')->findByFeed($this);

        $isUpToDate = TRUE;

        // Foreach feedData we check if we have a value for yesterday.
        /** @var \AppBundle\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            // A feed is up to date only if all its feedData are up to date.
            $isUpToDate = $isUpToDate && $feedData->isUpToDate($entityManager, $date, $frequencies);
        }

        return $isUpToDate;
    }
}
