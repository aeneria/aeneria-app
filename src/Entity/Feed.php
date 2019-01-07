<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;
use App\FeedObject\FeedObject;

/**
 * Feed
 *
 * @ORM\Table(name="feed")
 * @ORM\Entity(repositoryClass="App\Repository\FeedRepository")
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
            'CLASS' => 'App\FeedObject\Linky',
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
                'TEMPERATURE_MIN' => [
                    'UNIT' => '°C',
                ],
                'TEMPERATURE_MAX' => [
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
            'CLASS' => 'App\FeedObject\MeteoFrance',
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
     * Feed Object
     * @var FeedObject
     */
    private $catchedFeedObject = NULL;


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
     *
     * @return bool
     */
    public function isUpToDate(EntityManager $entityManager, \DateTime $date, array $frequencies)
    {
        // Get all feedData.
        $feedDataList = $entityManager->getRepository('App:FeedData')->findByFeed($this);

        $isUpToDate = TRUE;

        // Foreach feedData we check if we have a value for yesterday.
        /** @var \App\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            // A feed is up to date only if all its feedData are up to date.
            $isUpToDate = $isUpToDate && $feedData->isUpToDate($entityManager, $date, $frequencies);
        }

        return $isUpToDate;
    }

    /**
     * Get Date of last up to date data.
     * @param EntityManager $entityManager
     * @param $frequencies array of int from DataValue frequencies
     *
     * @return NULL|\DateTime
     */
    public function getLastUpToDate(EntityManager $entityManager)
    {
        // Get all feedData.
        $feedDataList = $entityManager->getRepository('App:FeedData')->findByFeed($this);

        $lastUpToDate = new \DateTime();
        $lastUpToDate->sub(new \DateInterval('P2D'));

        // Foreach feedData we get the last up to date value.
        /** @var \App\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            // A feed is up to date only if all its feedData are up to date.
            $feedDataLastUpToDate = $feedData->getLastUpToDate($entityManager);
            $lastUpToDate = min($lastUpToDate, $feedDataLastUpToDate);
        }

        $lastUpToDate->add(new \DateInterval('P1D'));

        return $lastUpToDate;
    }

    /**
     * @return \App\FeedObject\FeedObject
     */
    public function getFeedObject(EntityManager $entityManager) {
        if (empty($this->catchedFeedObject)) {
            $feedClass = Feed::FEED_TYPES[$this->feedType]['CLASS'];
            $this->catchedFeedObject = new $feedClass($this, $entityManager);
        }
        return $this->catchedFeedObject;
    }

    /**
     * Fetch data from last data to $date.
     * @param \DateTime $date
     * @param bool $force
     */
    public function fetchDataToDate(EntityManager $entityManager, \DateTime $date) {
        $lastUpToDate = $this->getLastUpToDate($entityManager);
        $lastUpToDate = new \DateTime($lastUpToDate->format("Y-m-d 00:00:00"));

        while($lastUpToDate <= $date) {
            $this->getFeedObject($entityManager)->fetchData($lastUpToDate);
            $lastUpToDate->add(new \DateInterval('P1D'));
        }
    }

    /**
     * Fetch data from last data for $date
     * @param \DateTime $date
     * @param bool $force
     */
    public function fetchDataForDate(EntityManager $entityManager, \DateTime $date, $force) {
        if ($force || !$this->isUpToDate($entityManager, $date, $this->getFeedObject($entityManager)::FREQUENCY)) {
            $this->getFeedObject($entityManager)->fetchData($date);
        }
    }
}
