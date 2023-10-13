<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\ConsentementDetail;
use App\GrdfAdict\Model\Token;

class MockAuthentificationClient extends AbstractApiClient implements AuthentificationClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConsentPageUrl(string $state, string $organisationName, string $firstnameHint = 'Prénom', string $lastnameHint = 'Nom', string $emailHint = 'Email'): string
    {
        return 'http://grdf-addict.fr/consent';
    }

    /**
     * {@inheritdoc}
     */
    public function requestConsentementDetail(string $code): ConsentementDetail
    {
        return ConsentementDetail::fromJson(<<<JSON
        {
            "access_token": "WeOAFUQA7KjyvWRujg6pqCNshq6pxJaC497Ubz3bku12lF4SW5Dws5",
            "token_type": "Bearer",
            "expires_in": 12600,
            "scope": "/adict/v2"
        }
        JSON);
    }

    /**
     * {@inheritdoc}
     */
    public function requestAuthorizationToken(): Token
    {
        return Token::fromJson(<<<JSON
        {
            "access_token": "WeOAFUQA7KjyvWRujg6pqCNshq6pxJaC497Ubz3bku12lF4SW5Dws5",
            "token_type": "Bearer",
            "expires_in": 12600,
            "id_token": 'qfsqdqsdfsdfsf'
            "scope": "/adict/v2"
        }
        JSON);
    }
}
