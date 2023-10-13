<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Model\Token;

/**
 * Implements DataConnect AuthorizeV1 API
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/authorize-v1/
 */
interface AuthorizeV1ClientInterface
{
    /**
     * Get a URL to DataConnect consent page.
     *
     * @param string $duration Durée du consentement demandé par l’application,
     * au format ISO 8601. Cette durée sera affichée au consommateur et ne peut
     * excéder 3 ans. (ex : P6M pour 6 mois)
     *
     * @param string $state Paramètre de sécurité permettant de maintenir l’état
     * entre la requête et la redirection.
     */
    public function getConsentPageUrl(string $duration, string $state): string;

    /**
     * Get DataConnectToken from a grant code.
     */
    public function requestTokenFromCode(string $code): Token;

    /**
     * Get DataConnectToken from a refreshToken.
     */
    public function requestTokenFromRefreshToken(string $refreshToken): Token;
}
