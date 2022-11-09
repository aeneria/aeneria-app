<?php

declare(strict_types=1);

namespace App\Entity;

use JsonSerializable;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Place
 */
class User implements UserInterface, Serializable, JsonSerializable
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    /** @var int */
    private $id;

    /** @var bool */
    private $active;

    /**
     * @var string
     * Good to know: username is an email
     */
    private $username;

    /** @var array */
    private $roles = [];

    /** @var string The hashed password */
    private $password;

    /** @var Place[] */
    private $places = [];

    /** @var Place[] */
    private $sharedPlaces;

    /** @var \DateTimeInterface */
    private $createdAt;
    /** @var \DateTimeInterface */
    private $updatedAt;
    /** @var \DateTimeInterface */
    private $lastLogin;

    public function getId(): ?int
    {
        return $this->id;
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
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPlaces(): iterable
    {
        return $this->places;
    }

    public function setPlaces(array $places): self
    {
        $this->places = $places;

        return $this;
    }

    public function getSharedPlaces(): ?iterable
    {
        return $this->sharedPlaces;
    }

    public function addSharedPlace(Place $place): self
    {
        $this->sharedPlaces[] = $place;

        return $this;
    }

    public function setSharedPlaces(array $places): self
    {
        $this->sharedPlaces = $places;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
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
     * @param serialized
     */
    public function unserialize($serialized)
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

    public function jsonSerialize()
    {
        if (!\is_array($places = $this->getPlaces())) {
            $places = \iterator_to_array($places);
        }

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
}
