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
}
