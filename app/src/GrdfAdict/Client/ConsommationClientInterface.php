<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\MeteringData;

/**
 * Implements DataConnect Metering Data V4
 */
interface ConsommationClientInterface
{
    /**
     * Get consumption between 2 dates for a usage point.
     *
     * Récupérer les données de consommation,
     * sur l'intervalle de mesure du compteur (par défaut 30 min)
     */
    public function requestConsoInformative(string $accessToken, string $pce, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData;

}
