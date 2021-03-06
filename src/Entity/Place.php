<?php

namespace App\Entity;

/**
 * Place
 */
class Place
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $icon = 'home';

    /** @var bool */
    private $public = false;

    /** @var User|null */
    private $user;

    /** @var User[]|null */
    private $allowedUsers = [];

    /** @var Feed[]|null */
    private $feeds = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function addFeed(Feed $feed): self
    {
        // If the feed we try to add is already there, we delete it
        $this->removeFeed($feed);

        $this->feeds[] = $feed;
        $feed->addPlace($this);

        return $this;
    }

    public function removeFeed(Feed $feed): self
    {
        foreach ($this->feeds as $key => $currentFeed) {
            if ($currentFeed->getId() && $currentFeed->getId() === $feed->getId()) {
                unset($this->feeds[$key]);
                $feed->removePlace($this);
            }
        }

        return $this;
    }

    /**
     * @param Feed[] $feeds
     */
    public function setFeeds(array $feeds): self
    {
        $this->feeds = $feeds;

        foreach ($feeds as $feed) {
            $feed->addPlace($this);
        }

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
            foreach ($feed->getFeedDatas() as $feedData) {
                $feedDatas[$feedData->getDataType()] = $feedData;
            }
        }

        return $feedDatas;
    }

    public function getFeedData(string $feedDataType): ?FeedData
    {
        if (!\array_key_exists($feedDataType, FeedData::getAllTypeLabels())) {
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

    public function setAllowedUsers(array $allowedUsers): self
    {
        $this->allowedUsers = $allowedUsers;

        return $this;
    }
}
