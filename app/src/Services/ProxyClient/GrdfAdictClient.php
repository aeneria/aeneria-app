<?php

declare(strict_types=1);

namespace App\Services\ProxyClient;

use Aeneria\GrdfAdictApi\Exception\GrdfAdictConsentException;
use Aeneria\GrdfAdictApi\Exception\GrdfAdictDataNotFoundException;
use Aeneria\GrdfAdictApi\Exception\GrdfAdictException;
use Aeneria\GrdfAdictApi\Exception\GrdfAdictQuotaExceededException;
use Aeneria\GrdfAdictApi\Model\InfoTechnique;
use Aeneria\GrdfAdictApi\Model\MeteringData;
use App\Services\SodiumCryptoService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * A service to use æneria-proxy grdf adict API.
 *
 * @see https://gitlab.com/aeneria/aeneria-proxy
 */
class GrdfAdictClient
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
                $this->router->generate('api.feed.grdf.consent.callback', [], RouterInterface::ABSOLUTE_URL)
            ),
        ];

        $response = $this->httpClient->request(
            'POST',
            \sprintf('%s/grdf-adict/authorize', $this->proxyUrl),
            [
                'body' => $body,
            ]
        );

        $this->checkResponse($response);

        return \json_decode($response->getContent());
    }

    public function requestInfoTechnique(string $encodedPce): InfoTechnique
    {
        $response = $this->httpClient->request(
            'GET',
            \sprintf(
                '%s/grdf-adict/%s/info-technique',
                $this->proxyUrl,
                $encodedPce
            )
        );

        $this->checkResponse($response);

        $content = \json_decode($response->getContent());
        $decrypted = $this->sodiumCryptoService->open(\urldecode($content->data));

        return InfoTechnique::fromJson($decrypted);
    }

    public function requestConsoInformative(
        $encodedPce,
        \DateTimeInterface $dateDebut,
        \DateTimeInterface $dateFin
    ): MeteringData {
        $response = $this->httpClient->request(
            'GET',
            \sprintf(
                '%s/grdf-adict/%s/conso-informative/%s/%s',
                $this->proxyUrl,
                $encodedPce,
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
                    throw new GrdfAdictConsentException($response->getContent(false), $code);
                case 404:
                    throw new GrdfAdictDataNotFoundException($response->getContent(false), $code);
                case 429:
                    throw new GrdfAdictQuotaExceededException($response->getContent(false), $code);
                default:
                    throw new GrdfAdictException($response->getContent(false), $code);
            }
        }
    }
}
