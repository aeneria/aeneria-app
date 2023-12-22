<?php

declare(strict_types=1);

namespace App\Services\ProxyClient;

use Aeneria\EnedisDataConnectApi\Exception\DataConnectConsentException;
use Aeneria\EnedisDataConnectApi\Exception\DataConnectDataNotFoundException;
use Aeneria\EnedisDataConnectApi\Exception\DataConnectException;
use Aeneria\EnedisDataConnectApi\Exception\DataConnectQuotaExceededException;
use Aeneria\EnedisDataConnectApi\Model\Address;
use Aeneria\EnedisDataConnectApi\Model\MeteringData;
use App\Services\JwtService;
use App\Services\SodiumCryptoService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * A service to use æneria-proxy enedis data connect API.
 *
 * @see https://gitlab.com/aeneria/aeneria-proxy
 */
class EnedisDataConnectClient
{
    public function __construct(
        private string $proxyUrl,
        private HttpClientInterface $httpClient,
        private RouterInterface $router,
        private SodiumCryptoService $sodiumCryptoService
    ) {}

    public function getConsentPageUrl(string $state): string
    {
        $body = [
            'state' => $state,
            'key' => \urlencode($this->sodiumCryptoService->getPublicKey()),
            'callback' => \urlencode(
                $this->router->generate('api.feed.enedis.consent.callback', [], RouterInterface::ABSOLUTE_URL)
            ),
        ];

        $response = $this->httpClient->request(
            'POST',
            \sprintf('%s/enedis-data-connect/authorize', $this->proxyUrl),
            [
                'body' => $body,
            ]
        );

        $this->checkResponse($response);

        return \json_decode($response->getContent());
    }

    public function requestUsagePointAdresse(string $encodedPdl): Address
    {
        $response = $this->httpClient->request(
            'GET',
            \sprintf(
                '%s/enedis-data-connect/%s/addresse',
                $this->proxyUrl,
                $encodedPdl
            )
        );

        $this->checkResponse($response);

        $content = \json_decode($response->getContent());
        $decrypted = $this->sodiumCryptoService->open(\urldecode($content->data));

        return Address::fromJson($decrypted);
    }

    public function requestConsumptionLoadCurve(
        $encodedPdl,
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin
    ): MeteringData {
        $response = $this->httpClient->request(
            'GET',
            \sprintf(
                '%s/enedis-data-connect/%s/consumption-load-curve/%s/%s',
                $this->proxyUrl,
                $encodedPdl,
                $dateDebut->format('Y-m-d'),
                $dateFin->format('Y-m-d')
            )
        );

        $this->checkResponse($response);

        $content = \json_decode($response->getContent());
        $decrypted = $this->sodiumCryptoService->open(\urldecode($content->data));

        return MeteringData::fromJson($decrypted);
    }

    public function requestDailyConsumption(
        $encodedPdl,
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin
    ): MeteringData {
        $response = $this->httpClient->request(
            'GET',
            \sprintf(
                '%s/enedis-data-connect/%s/daily-consumption/%s/%s',
                $this->proxyUrl,
                $encodedPdl,
                $dateDebut->format('Y-m-d'),
                $dateFin->format('Y-m-d')
            )
        );

        $this->checkResponse($response);

        $content = \json_decode($response->getContent());
        $decrypted = $this->sodiumCryptoService->open(\urldecode($content->data));

        return MeteringData::fromJson($decrypted);
    }

    private function checkResponse(ResponseInterface $response): void
    {
        $code = $response->getStatusCode();

        if (200 !== $code) {
            switch ($code) {
                case 403:
                    throw new DataConnectConsentException($response->getContent(false), $code);
                case 404:
                    throw new DataConnectDataNotFoundException($response->getContent(false), $code);
                case 429:
                    throw new DataConnectQuotaExceededException($response->getContent(false), $code);
                default:
                    throw new DataConnectException($response->getContent(false), $code);
            }
        }
    }
}
