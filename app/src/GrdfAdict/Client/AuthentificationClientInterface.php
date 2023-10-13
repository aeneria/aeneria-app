<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\ConsentementDetail;
use App\GrdfAdict\Model\Token;

/**
 * Implements GRDF adict authentification API
 *
 * @see https://site.grdf.fr/web/grdf-adict/technique/
 */
interface AuthentificationClientInterface
{
    /**
     * Get a URL to GRDF adict consent page.
     *
     * @param string $state Paramètre de sécurité permettant de maintenir l’état
     * entre la requête et la redirection.
     *
     * @param string $organisationName Nom de l'organisation qui demande le consentement.
     * Utilisé pour identifier visuellement l'organisation sur le site de GRDF.
     *
     * @param string $firstnameHint Paramètre pour préremplire le formulaire de consentement
     *
     * @param string $lastnameHint Paramètre pour préremplire le formulaire de consentement
     *
     * @param string $emailHint Paramètre pour préremplire le formulaire de consentement
     */
    public function getConsentPageUrl(string $state, string $organisationName, string $firstnameHint = 'Prénom', string $lastnameHint = 'Nom', string $emailHint = 'Email'): string;

    /**
     * Get Consentement Details from a grant code.
     */
    public function requestConsentementDetail(string $code): ConsentementDetail;

    /**
     * Get Authorization Token from credentials.
     */
    public function requestAuthorizationToken(): Token;
}
