<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="name", type="string", length=150)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=50)
     */
    private $icon = 'home';

    /**
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean")
     */
    private $public;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="places")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", cascade={"persist"})
     */
    private $allowedUsers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Feed", mappedBy="place")
     */
    private $feeds = [];

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

    public function setIcon(string $icon): Place
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
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

    public function setUser(User $user): Place
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function addFeed(Feed $feed): Place
    {
        // If the feed we try to add is already there, we delete it
        foreach( $this->feeds as $key => $currentFeed) {
            if ($currentFeed->getId() && $currentFeed->getId() === $feed->getId()) {
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

    public function getFeed(string $feedType): ?Feed
    {
        if (!\array_key_exists($feedType, Feed::getAllFeedTypes())) {
            throw new \InvalidArgumentException(\sprintf(
                'Le type de Feed %s n\'existe pas',
                $feedType
            ));
        }

        if ($this->feeds) {
            foreach ($this->feeds as $feed) {
                if ($feedType === $feed->getFeedType()) {
                    return $feed;
                }
            }
        }

        return null;
    }

    public function getFeedDatas(): array
    {
        $feedDatas = [];

        foreach ($this->feeds as $feed) {
            $feedDatas = \array_merge($feedDatas, \iterator_to_array($feed->getFeedDatas()));
        }

        return $feedDatas;
    }

    public function getFeedData(string $feedDataType): ?FeedData
    {
        if (!FeedData::getLabelFor($feedDataType)) {
            throw new \InvalidArgumentException(\sprintf(
                'Le type de Feed %s n\'existe pas',
                $feedDataType
            ));
        }

        if ($this->feeds) {
            foreach ($this->feeds as $feed) {
                if ($feedData = $feed->getFeedData($feedDataType)) {
                    return $feedData;
                }
            }
        }

        return null;
    }

    public function getAllowedUsers(): ?iterable
    {
        return $this->allowedUsers;
    }

    public function setAllowedUsers(array $allowedUsers): Place
    {
        $this->allowedUsers = $allowedUsers;

        return $this;
    }
}
