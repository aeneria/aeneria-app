<?php

declare(strict_types=1);

namespace App\GrdfAdict\Tests\Unit;

use App\GrdfAdict\Exception\GrdfAdictConsentException;
use App\GrdfAdict\Exception\GrdfAdictDataNotFoundException;
use App\GrdfAdict\Exception\GrdfAdictException;
use App\GrdfAdict\Exception\GrdfAdictQuotaExceededException;
use App\GrdfAdict\Model\Token;
use App\GrdfAdict\Client\AuthentificationClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class AuthentificationServiceTest extends TestCase
{
    public function testConsentPageUrl()
    {
        $service = new AuthentificationClient(
            HttpClient::create(),
            "endpoint",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $consentUrl = $service->getConsentPageUrl('state', 'organization name');

        self::assertSame('endpoint/openam/oauth2/realms/externeGrdf/authorize?client_id=clientId&state=state&scope=openid&response_type=code&redirect_uri=redirectUri&login_hint=Prénom;Nom;Email;organization name', $consentUrl);
    }

    public function testRequestToken()
    {
        $json = <<<JSON
        {
        "access_token": "ba42fe5a-0eaa-11e5-9813-4dd05b3a25f3",
        "scope": "/adict/v1",
        "token_type": "Bearer",
        "id_token": "16401220101758",
        "expires_in": 12600
        }
        JSON;
        $token = Token::fromJson($json);

        $httpClient = new MockHttpClient(
            new MockResponse($json)
        );

        $service = new AuthentificationClient(
            $httpClient,
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $tokenFromService = $service->requestAuthorizationToken();

        self::assertEquals($token->accessToken, $tokenFromService->accessToken);
        self::assertEquals($token->tokenType, $tokenFromService->tokenType);
        self::assertEquals($token->scope, $tokenFromService->scope);
        self::assertTrue($token->isAccessTokenStillValid());
    }

    public function test403Failure()
    {
        $httpClient = new MockHttpClient(
            new MockResponse('', ['http_code' => 403])
        );

        $service = new AuthentificationClient(
            $httpClient,
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $this->expectException(GrdfAdictConsentException::class);

        $service->requestAuthorizationToken();
    }

    public function test404Failure()
    {
        $httpClient = new MockHttpClient(
            new MockResponse('', ['http_code' => 404])
        );

        $service = new AuthentificationClient(
            $httpClient,
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $this->expectException(GrdfAdictDataNotFoundException::class);

        $service->requestAuthorizationToken();
    }

    public function test429Failure()
    {
        $httpClient = new MockHttpClient(
            new MockResponse('', ['http_code' => 429])
        );

        $service = new AuthentificationClient(
            $httpClient,
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $this->expectException(GrdfAdictQuotaExceededException::class);

        $service->requestAuthorizationToken();
    }

    public function testOtherFailure()
    {
        $httpClient = new MockHttpClient(
            new MockResponse('', ['http_code' => 500])
        );

        $service = new AuthentificationClient(
            $httpClient,
            "https://gw.hml.api.enedis.fr",
            'clientId',
            'clientSecrect',
            'redirectUri'
        );

        $this->expectException(GrdfAdictException::class);

        $service->requestAuthorizationToken();
    }
}
