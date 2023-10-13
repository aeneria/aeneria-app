<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Tests\Unit;

use App\EnedisDataConnect\Model\Token;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class TokenTest extends TestCase
{
    public function testHydratation()
    {
        $data = <<<JSON
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

        $token = Token::fromJson($data);

        self::assertInstanceOf(Token::class, $token);
        self::assertSame("WeOAFUQA7KjyvWRujg6pqCNshq6pxJaC497Ubz3bku12lF4SW5Dws5", $token->getAccessToken());
        self::assertEquals(\DateTimeImmutable::createFromFormat('U', (string) (int) '1542289239.976'), $token->getAccessTokenIssuedAt());
        self::assertTrue($token->isAccessTokenStillValid());
        self::assertSame("Aq3WgspVeiUCCSxtsRBvr88GIRKAibcmYtBSPReLOPL2wA", $token->getRefreshToken());
        self::assertEquals(\DateTimeImmutable::createFromFormat('U', (string) (int) '1542279238.976'), $token->getRefreshTokenIssuedAt());
        self::assertEquals("Bearer", $token->getTokenType());
        self::assertEquals("/v3/customers/usage_points/addresses.GET /v3/customers/usage_points/contracts.GET /v4/metering_data/daily_consumption.GET /v3/customers/contact_data.GET /v4/metering_data/consumption_max_power.GET /v4/metering_data/consumption_load_curve.GET /v3/customers/identity.GET", $token->getScope());
    }

    public function testSerialization()
    {
        $serializer = new Serializer(
            [
                new DateTimeNormalizer(),
                new ObjectNormalizer(null, null, null, new ReflectionExtractor()),
            ],
            [new JsonEncoder()]
        );

        $data = <<<JSON
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

        $token = Token::fromJson($data);
        $deserializedToken = $serializer->deserialize(
            $serializer->serialize($token, 'json'),
            Token::class,
            'json'
        );

        self::assertInstanceOf(Token::class, $deserializedToken);
        self::assertSame("WeOAFUQA7KjyvWRujg6pqCNshq6pxJaC497Ubz3bku12lF4SW5Dws5", $deserializedToken->getAccessToken());
        self::assertEquals(\DateTimeImmutable::createFromFormat('U', (string) (int) 1542289239.976), $deserializedToken->getAccessTokenIssuedAt());
        self::assertTrue($deserializedToken->isAccessTokenStillValid());
        self::assertSame("Aq3WgspVeiUCCSxtsRBvr88GIRKAibcmYtBSPReLOPL2wA", $deserializedToken->getRefreshToken());
        self::assertEquals(\DateTimeImmutable::createFromFormat('U', (string) (int) 1542279238.976), $deserializedToken->getRefreshTokenIssuedAt());
        self::assertEquals("Bearer", $deserializedToken->getTokenType());
        self::assertEquals("/v3/customers/usage_points/addresses.GET /v3/customers/usage_points/contracts.GET /v4/metering_data/daily_consumption.GET /v3/customers/contact_data.GET /v4/metering_data/consumption_max_power.GET /v4/metering_data/consumption_load_curve.GET /v3/customers/identity.GET", $deserializedToken->getScope());
    }
}
