<?php

namespace App\Services\AeneriaProxyClient;

use Aeneria\GrdfAdictApi\Exception\GrdfAdictConsentException;
use Aeneria\GrdfAdictApi\Exception\GrdfAdictDataNotFoundException;
use Aeneria\GrdfAdictApi\Exception\GrdfAdictException;
use Aeneria\GrdfAdictApi\Exception\GrdfAdictQuotaExceededException;
use Aeneria\GrdfAdictApi\Model\InfoTechnique;
use Aeneria\GrdfAdictApi\Model\MeteringData;
use App\Services\JwtService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * A service to use æneria-proxy grdf adict API.
 *
 * @see https://gitlab.com/aeneria/aeneria-proxy
 */
class GrdfAdictProxyClient
{
    /** @var string */
    private $proxyUrl;

    /** @var HttpClientInterface */
    private $httpClient;
    /** @var RouterInterface */
    private $router;
    /** @var JwtService */
    private $jwtService;

    public function __construct(
        string $proxyUrl,
        HttpClientInterface $httpClient,
        RouterInterface $router,
        JwtService $jwtService
    ) {
        $this->proxyUrl = $proxyUrl;

        $this->httpClient = $httpClient;
        $this->router = $router;
        $this->jwtService = $jwtService;
    }

    public function getConsentPageUrl(string $state): string
    {
        $body = [
            'state' => $state,
            'key' => $this->jwtService->getPublicKey(),
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

        return $response->getContent();
    }

    public function requestInfoTechnique(string $encodedPce): InfoTechnique
    {
        $response = $this->httpClient->request(
            'GET',
            \sprintf(
                '%s/grdf-adict/adict/v1/pce/%s/info-technique',
                $this->proxyUrl,
                $encodedPce
            )
        );

        $this->checkResponse($response);

        \openssl_private_decrypt(
            $response->getContent(),
            $decrypted,
            $this->jwtService->getPrivateKey(),
        );

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
                '%s/grdf-adict/adict/v1/pce/%s/conso-informative/%s/%s',
                $this->proxyUrl,
                $encodedPce,
                $dateDebut->format('Y-m-d'),
                $dateFin->format('Y-m-d')
            )
        );

        $this->checkResponse($response);

        \openssl_private_decrypt(
            $response->getContent(),
            $decrypted,
            $this->jwtService->getPrivateKey(),
        );

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