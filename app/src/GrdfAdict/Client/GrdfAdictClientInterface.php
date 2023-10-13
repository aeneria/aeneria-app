<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

/**
 * Meta-Client to access all API services
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/
 */
interface GrdfAdictClientInterface
{
    public function getAuthentificationClient(): AuthentificationClientInterface;

    public function getConsommationClient(): ConsommationClientInterface;

    public function getContratClient(): ContratClientInterface;
}
