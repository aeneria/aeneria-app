<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Place
 */
class Place implements \JsonSerializable
{
    private int $id;
    private string $name;
    private string $icon = 'home';
    private bool $public = false;
    private ?User $user;
    /** @var Collection<int, User> */
    private Collection $allowedUsers;
    /** @var Collection<int, Feed> */
    private Collection $feeds;

    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;

    /**
     * N'est pas hydraté automatiquement. à setter à la main si nécessaire
     */
    private ?\DateTimeImmutable $periodeMin = null;

    /**
     * N'est pas hydraté automatiquement. à setter à la main si nécessaire
     */
    private ?\DateTimeImmutable $periodeMax = null;

    public function __construct()
    {
        $this->allowedUsers = new ArrayCollection();
        $this->feeds = new ArrayCollection();
    }

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
        if (!$this->feeds->contains($feed)) {
            $this->feeds->add($feed);
            $feed->addPlace($this);
        }

        return $this;
    }

    public function removeFeed(Feed $feed): self
    {
        if ($this->feeds->removeElement($feed)) {
            $feed->removePlace($this);
        }

        return $this;
    }

    /**
     * @param Feed[] $feeds
     */
    public function setFeeds(array $feeds): self
    {
        $this->feeds = new ArrayCollection($feeds);

        foreach ($feeds as $feed) {
            $feed->addPlace($this);
        }

        return $this;
    }

    public function getFeeds(): Collection
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

        return $this
            ->feeds
            ->findFirst(
                fn (int $key, Feed $feed) => $feedType === $feed->getFeedType()
            )
        ;
    }

    public function findFeed(int $feedId): ?Feed
    {
        return $this
            ->feeds
            ->findFirst(
                fn (int $key, Feed $feed) => $feedId === $feed->getId()
            )
        ;
    }

    /**
     * @return FeedData[]
     */
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

        foreach ($this->feeds as $feed) {
            if ($feedData = $feed->getFeedData($feedDataType)) {
                return $feedData;
            }
        }

        return null;
    }

    public function getAllowedUsers(): Collection
    {
        return $this->allowedUsers;
    }

    /**
     * @param User[] $allowedUsers
     */
    public function setAllowedUsers(array $allowedUsers): self
    {
        $this->allowedUsers = new ArrayCollection($allowedUsers);

        return $this;
    }

    public function setPeriodeAmplitude(?\DateTimeImmutable $min, ?\DateTimeImmutable $max): self
    {
        $this->periodeMin = $min;
        $this->periodeMax = $max;

        return $this;
    }

    public function getPeriodeAmplitude(): array
    {
        return [$this->periodeMin, $this->periodeMax];
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        $feedList = \iterator_to_array($this->getFeeds());

        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => 'home',
            'feedList' => \array_map(
                function (Feed $feed) {
                    return $feed->jsonSerialize();
                },
                $feedList
            ),
            'allowedUsers' => User::toOptionList($this->allowedUsers),
            'public' => $this->isPublic(),
            'periodeMin' => $this->periodeMin,
            'periodeMax' => $this->periodeMax,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
