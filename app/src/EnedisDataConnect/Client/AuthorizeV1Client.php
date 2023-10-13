<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Model\Token;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Implements AuthorizeV1 API
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/authorize-v1/
 */
class AuthorizeV1Client extends AbstractApiClient implements AuthorizeV1ClientInterface
{
    public const GRANT_TYPE_CODE = 'authorization_code';
    public const GRANT_TYPE_TOKEN = 'refresh_token';

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var string */
    private $authEndpoint;
    /** @var string */
    private $tokenEndpoint;

    /** @var string */
    private $clientId;
    /** @var string */
    private $clientSecret;
    /** @var string */
    private $redirectUri;

    public function __construct(
        HttpClientInterface $httpClient,
        string $authEndpoint,
        string $tokenEndpoint,
        string $clientId,
        string $clientSecret,
        string $redirectUri
    ) {
        $this->httpClient = $httpClient;

        $this->authEndpoint = $authEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsentPageUrl(string $duration, string $state): string
    {
        return \sprintf(
            '%s/dataconnect/v1/oauth2/authorize?client_id=%s&response_type=code&state=%s&duration=%s',
            $this->authEndpoint,
            $this->clientId,
            $state,
            $duration
        );
    }

    /**
     * {@inheritdoc}
     */
    public function requestTokenFromCode(string $code): Token
    {
        return $this->requestToken(self::GRANT_TYPE_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function requestTokenFromRefreshToken(string $refreshToken): Token
    {
        return $this->requestToken(self::GRANT_TYPE_TOKEN, $refreshToken);
    }

    private function requestToken(string $grantType, string $codeOrToken): Token
    {
        $query = [
            'redirect_uri' => $this->redirectUri,
        ];

        $body = [
            'grant_type' => $grantType,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        switch ($grantType) {
            case self::GRANT_TYPE_CODE:
                $body['code'] = $codeOrToken;
                break;
            case self::GRANT_TYPE_TOKEN:
                $body['refresh_token'] = $codeOrToken;
                break;
            default:
                throw new \InvalidArgumentException(\sprintf(
                    'Only "%s" or "%s" grant types are supported',
                    self::GRANT_TYPE_TOKEN,
                    self::GRANT_TYPE_CODE
                ));
        }

        $response = $this->httpClient->request(
            'POST',
            \sprintf('%s/v1/oauth2/token', $this->tokenEndpoint),
            [
                'query' => $query,
                'body' => $body,
            ]
        );

        $this->checkResponse($response);

        return Token::fromJson($response->getContent());
    }
}
