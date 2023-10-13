<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

/**
 * Meta-Client to access all API services
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/
 */
interface DataConnectClientInterface
{
    public function getAuthorizeV1Client(): AuthorizeV1ClientInterface;

    public function getMeteringDataV4Client(): MeteringDataV4ClientInterface;

    public function getCustomersClient(): CustomersClientInterface;
}
