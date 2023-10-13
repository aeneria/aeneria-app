<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Meta-Client to access all API services
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/
 */
class DataConnectClient implements DataConnectClientInterface
{
    /** @var AuthorizeV1Client */
    private $authorizeV1Client;
    /** @var MeteringDataV4Client */
    private $meteringDataV4Client;
    /** @var CustomersClient */
    private $customersClient;

    public function __construct(HttpClientInterface $httpClient, string $authEndpoint, string $tokenEndpoint, string $dataEndpoint, string $clientId, string $clientSecret, string $redirectUri)
    {
        $this->authorizeV1Client = new AuthorizeV1Client($httpClient, $authEndpoint, $tokenEndpoint, $clientId, $clientSecret, $redirectUri);
        $this->meteringDataV4Client = new MeteringDataV4Client($httpClient, $dataEndpoint);
        $this->customersClient = new CustomersClient($httpClient, $dataEndpoint);
    }

    public function getAuthorizeV1Client(): AuthorizeV1ClientInterface
    {
        return $this->authorizeV1Client;
    }

    public function getMeteringDataV4Client(): MeteringDataV4ClientInterface
    {
        return $this->meteringDataV4Client;
    }

    public function getCustomersClient(): CustomersClientInterface
    {
        return $this->customersClient;
    }
}
