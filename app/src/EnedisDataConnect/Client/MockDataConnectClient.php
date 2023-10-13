<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

class MockDataConnectClient implements DataConnectClientInterface
{
    /** @var AuthorizeV1ClientInterface */
    private $authorizeV1Client;
    /** @var MeteringDataV4ClientInterface */
    private $meteringDataV4Client;
    /** @var CustomersClientInterface */
    private $customersClient;

    public function __construct()
    {
        $this->authorizeV1Client = new MockAuthorizeV1Client();
        $this->meteringDataV4Client = new MockMeteringDataV4Client();
        $this->customersClient = new MockCustomersClient();
    }

    public function getAuthorizeV1Client(): AuthorizeV1ClientInterface
    {
        return $this->authorizeV1Client;
    }

    public function getMeteringDataV4Client(): MeteringDataV4ClientInterface
    {
        return $this->meteringDataV4Client;
    }

    public function getCustomersClient(): CustomersClientInterface
    {
        return $this->customersClient;
    }
}
