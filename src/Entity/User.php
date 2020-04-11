<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User implements UserInterface, Serializable
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", options={"default" : true})
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Place", mappedBy="user")
     */
    private $places;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Place", mappedBy="allowedUsers")
     */
    private $sharedPlaces;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setActive(bool $isActive)
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
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
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
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

    public function getSharedPlaces(): iterable
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
            $this->sharedPlaces
        ) = \json_decode($serialized);
    }
}
