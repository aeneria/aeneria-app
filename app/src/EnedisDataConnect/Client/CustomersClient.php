<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Model\Address;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Implements Customers API
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/customers/
 */
class CustomersClient extends AbstractApiClient implements CustomersClientInterface
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var string */
    private $dataEndpoint;

    public function __construct(HttpClientInterface $httpClient, string $dataEndpoint)
    {
        $this->httpClient = $httpClient;
        $this->dataEndpoint = $dataEndpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function requestUsagePointAdresse(string $accessToken, string $usagePointId): Address
    {
        $response = $this->requestCustomersData('usage_points/addresses', $accessToken, $usagePointId);

        return Address::fromJson($response);
    }

    private function requestCustomersData(string $endpoint, string $accessToken, string $usagePointId): string
    {
        $response = $this->httpClient->request(
            'GET',
            \sprintf('%s/v3/customers/%s', $this->dataEndpoint, $endpoint),
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
                'auth_bearer' => $accessToken,
                'query' => [
                    'usage_point_id' => $usagePointId,
                ],
            ]
        );

        $this->checkResponse($response);

        return $response->getContent();
    }
}
