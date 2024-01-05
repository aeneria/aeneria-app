<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Notification
 */
class Notification implements \JsonSerializable
{
    public const LEVEL_ERROR = 'danger';
    public const LEVEL_SUCCESS = 'success';
    public const LEVEL_INFO = 'information';

    public const TYPE_DATA_IMPORT = 'data_import';
    public const TYPE_DATA_FETCH = 'data_fetch';
    public const TYPE_TOO_MANY_FETCH_ERROR = 'too_many_fetch_error';

    private int $id = 0;
    private string $type;
    private string $level;
    private ?User $user;
    private ?Place $place;
    private \DateTimeInterface $date;
    private string $message;

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
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'level' => $this->level,
            'user' => $this->user,
            'place' => $this->place,
            'date' => $this->date,
            'message' => $this->message,
        ];
    }
}
