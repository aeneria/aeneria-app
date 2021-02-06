<?php

namespace App\Entity;

/**
 * Notification
 */
class Notification
{
    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var string */
    private $level;

    /** @var User|null */
    private $user;

    /** @var Place|null */
    private $place;

    /** @var \DateTimeInterface */
    private $date;

    /** @var string */
    private $message;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
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

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->param;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
