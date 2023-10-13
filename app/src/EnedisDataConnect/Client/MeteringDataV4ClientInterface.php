<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Model\MeteringData;

/**
 * Implements DataConnect Metering Data V4
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/metering-data-v4/
 */
interface MeteringDataV4ClientInterface
{
    /**
     * Get consumption load curve between 2 dates for a usage point.
     *
     * Récupérer la puissance moyenne consommée quotidiennement,
     * sur l'intervalle de mesure du compteur (par défaut 30 min)
     */
    public function requestConsumptionLoadCurve(string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData;

    /**
     * Get production load curve between 2 dates for a usage point.
     *
     * Récupérer la puissance moyenne produite quotidiennement,
     * sur l'intervalle de mesure du compteur (par défaut 30 min)
     */
    public function requestProductionLoadCurve(string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData;

    /**
     * Get daily consumption between 2 dates for a usage point.
     *
     * Récupérer la consommation quotidienne
     */
    public function requestDailyConsumption(string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData;

    /**
     * Get daily production between 2 dates for a usage point.
     *
     * Récupérer la production quotidienne
     */
    public function requestDailyProduction(string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData;
}
