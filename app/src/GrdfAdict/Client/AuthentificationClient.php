<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\ConsentementDetail;
use App\GrdfAdict\Model\Token;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Implements Authentification API
 *
 * @see https://site.grdf.fr/web/grdf-adict/technique/
 */
class AuthentificationClient extends AbstractApiClient implements AuthentificationClientInterface
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var string */
    private $authEndpoint;

    /** @var string */
    private $clientId;
    /** @var string */
    private $clientSecret;
    /** @var string */
    private $redirectUri;

    public function __construct(
        HttpClientInterface $httpClient,
        string $authEndpoint,
        string $clientId,
        string $clientSecret,
        string $redirectUri
    ) {
        $this->httpClient = $httpClient;

        $this->authEndpoint = $authEndpoint;

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsentPageUrl(string $state, string $organisationName, string $firstnameHint = 'Prénom', string $lastnameHint = 'Nom', string $emailHint = 'Email'): string
    {
        return \sprintf(
            '%s/openam/oauth2/realms/externeGrdf/authorize?client_id=%s&state=%s&scope=openid&response_type=code&redirect_uri=%s&login_hint=%s;%s;%s;%s',
            $this->authEndpoint,
            $this->clientId,
            $state,
            $this->redirectUri,
            $firstnameHint,
            $lastnameHint,
            $emailHint,
            $organisationName,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function requestConsentementDetail(string $code): ConsentementDetail
    {
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ];

        $response = $this->httpClient->request(
            'POST',
            \sprintf('%s/openam/oauth2/realms/externeGrdf/access_token', $this->authEndpoint),
            [
                'body' => $body,
            ]
        );

        $this->checkResponse($response);

        return ConsentementDetail::fromJson($response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function requestAuthorizationToken(): Token
    {
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => '/adict/v2',
        ];

        $response = $this->httpClient->request(
            'POST',
            \sprintf('%s/openam/oauth2/realms/externeGrdf/access_token', $this->authEndpoint),
            [
                'body' => $body,
            ]
        );

        $this->checkResponse($response);

        return Token::fromJson($response->getContent());
    }
}
