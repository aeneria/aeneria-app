<?php

namespace App\Entity;

/**
 * PendingAction
 */
class PendingAction
{
    const TOKEN_LENGTH = 10;

    /** @var int */
    private $id;

    /** @var string */
    private $token;

    /** @var User|null */
    private $user;

    /** @var string */
    private $action;

    /** @var \DateTimeInterface */
    private $expirationDate;

    /** @var array */
    private $param = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

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

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getExpirationDate(): \DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeImmutable $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getParam(): array
    {
        return $this->param;
    }

    public function getSingleParam(string $name)
    {
        return $this->param[$name] ?? null;
    }

    public function existParam(string $name): bool
    {
        return \array_key_exists($name, $this->param);
    }

    public function setParam(array $param): self
    {
        $this->param = $param;

        return $this;
    }

    public function setSingleParam(string $name, $value): self
    {
        $this->param[$name] = $value;

        return $this;
    }
}
