<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Model\Token;

class MockAuthorizeV1Client extends AbstractApiClient implements AuthorizeV1ClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConsentPageUrl(string $duration, string $state): string
    {
        return 'http://enedis-dataconnect.fr/consent';
    }

    /**
     * {@inheritdoc}
     */
    public function requestTokenFromCode(string $code): Token
    {
        return Token::fromJson(<<<JSON
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
        JSON);
    }

    /**
     * {@inheritdoc}
     */
    public function requestTokenFromRefreshToken(string $refreshToken): Token
    {
        return Token::fromJson(<<<JSON
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
        JSON);
    }
}
