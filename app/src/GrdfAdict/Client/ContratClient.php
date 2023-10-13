<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\InfoTechnique;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Implements Customers API
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/customers/
 */
class ContratClient extends AbstractApiClient implements ContratClientInterface
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
    public function requestInfoTechnique(string $accessToken, string $pce): InfoTechnique
    {
        $response = $this->httpClient->request(
            'GET',
            \sprintf('%s/adict/v2/pce/%s/donnees_techniques', $this->dataEndpoint, $pce),
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
                'auth_bearer' => $accessToken,
            ]
        );

        $this->checkResponse($response);

        return InfoTechnique::fromJson($response->getContent());
    }
}
