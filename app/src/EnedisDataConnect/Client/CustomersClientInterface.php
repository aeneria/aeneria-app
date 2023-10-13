<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Model\Address;

/**
 * Implements DataConnect Customers API
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/customers/
 */
interface CustomersClientInterface
{
    public function requestUsagePointAdresse(string $accessToken, string $usagePointId): Address;
}
