<?php

namespace App\Entity;

use App\FeedObject\FeedObject;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="name", type="string", length=150, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="feed_type", type="string", length=150, unique=true)
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Place", inversedBy="feeds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $place;


    /**
     * Feed Object
     * @var FeedObject
     */
    private $catchedFeedObject = NULL;

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

    public function setPlace(Place $place): Feed
    {
        $this->place = $place;

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    /**
     * Check if there's data in DB for $date for all $feed's feedData and for all $frequencies.
     */
    public function isUpToDate(EntityManager $entityManager, \DateTime $date, array $frequencies): bool
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
     */
    public function getLastUpToDate(EntityManager $entityManager): ?\DateTime
    {
        // Get all feedData.
        $feedDataList = $entityManager->getRepository('App:FeedData')->findByFeed($this);

        $lastUpToDate = new \DateTime("2 days ago");

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

    public function getFeedObject(EntityManager $entityManager): FeedObject
    {
        if (empty($this->catchedFeedObject)) {
            $feedClass = Feed::FEED_TYPES[$this->feedType]['CLASS'];
            $this->catchedFeedObject = new $feedClass($this, $entityManager);
        }
        return $this->catchedFeedObject;
    }

    /**
     * Fetch data from last data to $date.
     */
    public function fetchDataUntilLastUpdateTo(EntityManager $entityManager, \DateTime $date): void
    {
        $lastUpToDate = $this->getLastUpToDate($entityManager);
        $lastUpToDate = new \DateTime($lastUpToDate->format("Y-m-d 00:00:00"));

        while($lastUpToDate <= $date) {
            if (!$this->isUpToDate($entityManager, $date, $this->getFeedObject($entityManager)::FREQUENCY)) {
                $this->getFeedObject($entityManager)->fetchData($lastUpToDate);
            }
            $lastUpToDate->add(new \DateInterval('P1D'));
        }
    }

    /**
     * Fetch data from last data for $date,
     * if $force is set to true, update data even if there are already ones.
     */
    public function fetchDataFor(EntityManager $entityManager, \DateTime $date, $force): void
    {
        if ($force || !$this->isUpToDate($entityManager, $date, $this->getFeedObject($entityManager)::FREQUENCY)) {
            $this->getFeedObject($entityManager)->fetchData($date);
        }
    }
}
