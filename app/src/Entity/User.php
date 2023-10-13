<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;
use Serializable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Place
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface, Serializable, JsonSerializable
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    private int $id;
    private bool $active;
    private string $username;
    private array $roles = [];
    /** The hashed password */
    private string $password;
    /** @var Collection<int, Place> */
    private Collection $places;
    /** @var Collection<int, Place> */
    private Collection $sharedPlaces;
    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;
    private ?\DateTimeInterface $lastLogin;

    public function __construct()
    {
        $this->places = new ArrayCollection();
        $this->sharedPlaces = new ArrayCollection();
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

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function setActive(bool $isActive): self
    {
        $this->active = $isActive;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): string
    {
        return (string) $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return \array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin(): bool
    {
        return \in_array(self::ROLE_ADMIN, $this->roles);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return 'bcrypt';
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        // Nothing to do there
    }

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function setPlaces(array $places): self
    {
        $this->places = new ArrayCollection($places);

        return $this;
    }

    public function getSharedPlaces(): Collection
    {
        return $this->sharedPlaces;
    }

    public function addSharedPlace(Place $place): self
    {
        if (!$this->sharedPlaces->contains($place)) {
            $this->sharedPlaces->add($place);
        }

        return $this;
    }

    public function setSharedPlaces(array $places): self
    {
        $this->sharedPlaces = new ArrayCollection($places);

        return $this;
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

    public function getLastLogin(): \DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Check if user can see place's data
     *
     * A user can see place's data if :
     *  - Place is public
     *  - he owns the place
     *  - someone shared the place with him
     */
    public function canSee(Place $askedPlace, bool $userCanSharePlace = true, bool $placeCanBePublic = true): bool
    {
        if ($placeCanBePublic && $askedPlace->isPublic()) {
            return true;
        }

        $askedPlaceId = $askedPlace->getId();

        foreach ($this->getPlaces() as $place) {
            if ($askedPlaceId === $place->getId()) {
                return true;
            }
        }

        if ($userCanSharePlace) {
            foreach ($this->getSharedPlaces() as $place) {
                if ($askedPlaceId === $place->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canEdit(Place $askedPlace): bool
    {
        $askedPlaceId = $askedPlace->getId();

        foreach ($this->getPlaces() as $place) {
            if ($askedPlaceId === $place->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Serializes the content of the current User object
     * @return string
     */
    public function serialize()
    {
        return \json_encode([
            $this->id,
            $this->active,
            $this->username,
            $this->password,
            $this->places,
            $this->roles,
            $this->sharedPlaces,
            $this->createdAt,
            $this->updatedAt,
            $this->lastLogin,
        ]);
    }

    /**
     * Unserializes the given string in the current User object
     */
    public function unserialize($serialized): mixed
    {
        list(
            $this->id,
            $this->active,
            $this->username,
            $this->password,
            $this->places,
            $this->roles,
            $this->sharedPlaces,
            $this->createdAt,
            $this->updatedAt,
            $this->lastLogin
        ) = \json_decode($serialized);
    }

    public function jsonSerialize(): mixed
    {
        $places = \iterator_to_array($this->getPlaces());

        return [
            'id' => $this->id,
            'active' => $this->active,
            'username' => $this->username,
            'places' => \array_map(
                function (Place $place) {
                    return $place->jsonSerialize();
                },
                $places
            ),
            'roles' => $this->roles,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'lastLogin' => $this->lastLogin,
        ];
    }

    /**
     * @param iterable<User> $userList
     */
    public static function toOptionList(?iterable $userList): array
    {
        $userList = \is_array($userList) ? $userList : \iterator_to_array($userList);

        return $userList ? \array_map(
            function (User $user) {
                return ['id' => $user->getId(), 'username' => $user->getUsername()];
            },
            $userList
        ) : [];
    }
}
