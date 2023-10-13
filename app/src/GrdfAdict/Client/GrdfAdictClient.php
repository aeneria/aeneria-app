<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Meta-Client to access all API services
 *
 * @see https://site.grdf.fr/web/grdf-adict/technique/
 */
class GrdfAdictClient implements GrdfAdictClientInterface
{
    /** @var AuthentificationClient */
    private $authentificationClient;
    /** @var ConsommationClient */
    private $consommationClient;
    /** @var ContratClient */
    private $contratClient;

    public function __construct(HttpClientInterface $httpClient, string $authEndpoint, string $dataEndpoint, string $clientId, string $clientSecret, string $redirectUri)
    {
        $this->authentificationClient = new AuthentificationClient($httpClient, $authEndpoint, $clientId, $clientSecret, $redirectUri);
        $this->consommationClient = new ConsommationClient($httpClient, $dataEndpoint);
        $this->contratClient = new ContratClient($httpClient, $dataEndpoint);
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
