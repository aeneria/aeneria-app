<?php

namespace App\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Pace
 *
 * @ORM\Table(name="place")
 * @ORM\Entity(repositoryClass="App\Repository\PlaceRepository")
 */
class Place
{
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
     * @ORM\OneToMany(targetEntity="App\Entity\Feed", mappedBy="place")
     */
    private $feeds;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Place
    {
        $this->id = $id;

        return $this;
    }

    public function setName(string $name): Place
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setPublic(bool $public): Place
    {
        $this->public = $public;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setCreator(int $creator): Place
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCreator(): ?int
    {
        return $this->creator;
    }

    public function addFeed(Feed $feed)
    {
        // If the feed we try to add is already there, we delete it
        foreach( $this->feeds as $key => $currentFeed) {
            if ($currentFeed->getId() === $feed->getId()) {
                unset($this->feeds[$key]);
            }
        }

        $this->feeds[] = $feed;
        $feed->setPlace($this);

        return $this;
    }

    public function getFeeds(): iterable
    {
        return $this->feeds;
    }
}
