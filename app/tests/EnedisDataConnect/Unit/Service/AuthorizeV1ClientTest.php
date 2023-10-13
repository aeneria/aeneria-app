<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Tests\Unit;

use App\EnedisDataConnect\Exception\DataConnectConsentException;
use App\EnedisDataConnect\Exception\DataConnectDataNotFoundException;
use App\EnedisDataConnect\Exception\DataConnectException;
use App\EnedisDataConnect\Exception\DataConnectQuotaExceededException;
use App\EnedisDataConnect\Model\Token;
use App\EnedisDataConnect\Client\AuthorizeV1Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class AuthorizeV1ClientTest extends TestCase
{
    public function testConsentPageUrl()
    {
        $service = new AuthorizeV1Client(
            HttpClient::create(),
            'endpoint',
            "endpoint",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $consentUrl = $service->getConsentPageUrl('duration', 'state');

        self::assertSame('endpoint/dataconnect/v1/oauth2/authorize?client_id=clientId&response_type=code&state=state&duration=duration', $consentUrl);
    }

    public function testRequestTokenFromCode()
    {
        $json = <<<JSON
        {
            "access_token": "WeOAFUQA7KjyvWRujg6pqCNshq6pxJaC497Ubz3bku12lF4SW5Dws5",
            "token_type": "Bearer",
            "expires_in": 12600,
            "refresh_token": "Aq3WgspVeiUCCSxtsRBvr88GIRKAibcmYtBSPReLOPL2wA",
            "scope": "/v3/customers/usage_points/addresses.GET /v3/customers/usage_points/contracts.GET /v4/metering_data/daily_consumption.GET /v3/customers/contact_data.GET /v4/metering_data/consumption_max_power.GET /v4/metering_data/consumption_load_curve.GET /v3/customers/identity.GET",
            "refresh_token_issued_at": "1542279238976",
            "issued_at": "1542289239976",
            "usage_points_id": "12546852467895"
        }
        JSON;
        $token = Token::fromJson($json);

        $httpClient = new MockHttpClient(
            new MockResponse($json)
        );

        $service = new AuthorizeV1Client(
            $httpClient,
            'http://endpoint.fr',
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $tokenFromService = $service->requestTokenFromCode('code');

        self::assertEquals($token->getAccessToken(), $tokenFromService->getAccessToken());
        self::assertEquals($token->getTokenType(), $tokenFromService->getTokenType());
        self::assertEquals($token->getScope(), $tokenFromService->getScope());
        self::assertEquals($token->getAccessTokenIssuedAt(), $tokenFromService->getAccessTokenIssuedAt());
        self::assertEquals($token->getRefreshToken(), $tokenFromService->getRefreshToken());
        self::assertEquals($token->getRefreshTokenIssuedAt(), $tokenFromService->getRefreshTokenIssuedAt());
        self::assertEquals($token->getUsagePointsId(), $tokenFromService->getUsagePointsId());
    }

    public function testRequestTokenFromRefreshToken()
    {
        $json = <<<JSON
        {
            "access_token": "WeOAFUQA7KjyvWRujg6pqCNshq6pxJaC497Ubz3bku12lF4SW5Dws5",
            "token_type": "Bearer",
            "expires_in": 12600,
            "refresh_token": "Aq3WgspVeiUCCSxtsRBvr88GIRKAibcmYtBSPReLOPL2wA",
            "scope": "/v3/customers/usage_points/addresses.GET /v3/customers/usage_points/contracts.GET /v4/metering_data/daily_consumption.GET /v3/customers/contact_data.GET /v4/metering_data/consumption_max_power.GET /v4/metering_data/consumption_load_curve.GET /v3/customers/identity.GET",
            "refresh_token_issued_at": "1542279238976",
            "issued_at": "1542289239976",
            "usage_points_id": "12546852467895"
        }
        JSON;
        $token = Token::fromJson($json);

        $httpClient = new MockHttpClient(
            new MockResponse($json)
        );

        $service = new AuthorizeV1Client(
            $httpClient,
            'http://endpoint.fr',
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $tokenFromService = $service->requestTokenFromRefreshToken('code');

        self::assertEquals($token->getAccessToken(), $tokenFromService->getAccessToken());
        self::assertEquals($token->getTokenType(), $tokenFromService->getTokenType());
        self::assertEquals($token->getScope(), $tokenFromService->getScope());
        self::assertEquals($token->getAccessTokenIssuedAt(), $tokenFromService->getAccessTokenIssuedAt());
        self::assertEquals($token->getRefreshToken(), $tokenFromService->getRefreshToken());
        self::assertEquals($token->getRefreshTokenIssuedAt(), $tokenFromService->getRefreshTokenIssuedAt());
        self::assertEquals($token->getUsagePointsId(), $tokenFromService->getUsagePointsId());
    }

    public function test403Failure()
    {
        $httpClient = new MockHttpClient(
            new MockResponse('', ['http_code' => 403])
        );

        $service = new AuthorizeV1Client(
            $httpClient,
            'http://endpoint.fr',
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $this->expectException(DataConnectConsentException::class);

        $service->requestTokenFromRefreshToken('code');
    }

    public function test404Failure()
    {
        $httpClient = new MockHttpClient(
            new MockResponse('', ['http_code' => 404])
        );

        $service = new AuthorizeV1Client(
            $httpClient,
            'http://endpoint.fr',
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $this->expectException(DataConnectDataNotFoundException::class);

        $service->requestTokenFromRefreshToken('code');
    }

    public function test429Failure()
    {
        $httpClient = new MockHttpClient(
            new MockResponse('', ['http_code' => 429])
        );

        $service = new AuthorizeV1Client(
            $httpClient,
            'http://endpoint.fr',
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $this->expectException(DataConnectQuotaExceededException::class);

        $service->requestTokenFromRefreshToken('code');
    }

    public function testOtherFailure()
    {
        $httpClient = new MockHttpClient(
            new MockResponse('', ['http_code' => 500])
        );

        $service = new AuthorizeV1Client(
            $httpClient,
            'http://endpoint.fr',
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $this->expectException(DataConnectException::class);

        $service->requestTokenFromRefreshToken('code');
    }
}
