<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\MeteringData;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Implements Metering Data V4
 */
class ConsommationClient extends AbstractApiClient implements ConsommationClientInterface
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
    public function requestConsoInformative(string $accessToken, string $pce, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData
    {
        $response = $this->httpClient->request(
            'GET',
            \sprintf('%s/adict/v2/pce/%s/donnees_consos_informatives', $this->dataEndpoint, $pce),
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
                'auth_bearer' => $accessToken,
                'query' => [
                    'date_debut' => $start->format('Y-m-d'),
                    'date_fin' => $end->format('Y-m-d'),
                ],
            ]
        );

        $this->checkResponse($response);

        return MeteringData::fromJson($response->getContent());
    }
}
