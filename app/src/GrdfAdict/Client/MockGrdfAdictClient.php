<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

class MockGrdfAdictClient implements GrdfAdictClientInterface
{
    /** @var MockAuthentificationClient */
    private $authentificationClient;
    /** @var MockConsommationClient */
    private $consommationClient;
    /** @var MockContratClient */
    private $contratClient;

    public function __construct()
    {
        $this->authentificationClient = new MockAuthentificationClient();
        $this->consommationClient = new MockConsommationClient();
        $this->contratClient = new MockContratClient();
    }

    public function getAuthentificationClient(): AuthentificationClientInterface
    {
        return $this->authentificationClient;
    }

    public function getConsommationClient(): ConsommationClientInterface
    {
        return $this->consommationClient;
    }

    public function getContratClient(): ContratClientInterface
    {
        return $this->contratClient;
    }
}
