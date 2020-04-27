<?php

namespace App\Entity;

/**
 * Place
 */
class Place
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $icon = 'home';

    /**
     * @var bool
     */
    private $public;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var User[]|null
     */
    private $allowedUsers;

    /**
     * @var Feed[]|null
     */
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
        foreach ($this->feeds as $key => $currentFeed) {
            if ($currentFeed->getId() && $currentFeed->getId() === $feed->getId()) {
                unset($this->feeds[$key]);
            }
        }

        $this->feeds[] = $feed;
        $feed->setPlace($this);

        return $this;
    }

    public function setFeeds(array $feeds): self
    {
        $this->feeds = $feeds;

        return $this;
    }

    public function getFeeds(): iterable
    {
        return $this->feeds;
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
