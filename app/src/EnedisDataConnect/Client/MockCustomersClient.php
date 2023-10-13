<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Model\Address;

class MockCustomersClient extends AbstractApiClient implements CustomersClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function requestUsagePointAdresse(string $accessToken, string $usagePointId): Address
    {
        $json = <<<JSON
        {
          "customer": {
            "customer_id": "1358019319",
            "usage_points": [
              {
                "usage_point": {
                  "usage_point_id": "
        JSON
        ;
        $json .= $usagePointId;
        $json .= <<<JSON
                  ",
                  "usage_point_status": "com",
                  "meter_type": "AMM",
                  "usage_point_addresses": {
                    "street": "2 bis rue du capitaine Flam",
                    "locality": "lieudit Tourtouze",
                    "postal_code": "32400",
                    "insee_code": "32244",
                    "city": "Maulichères",
                    "country": "France",
                    "geo_points": {
                      "latitude": "43.687253",
                      "longitude": "-0.087957",
                      "altitude": "148"
                    }
                  }
                }
              }
            ]
          }
        }
        JSON;

        return Address::fromJson($json);
    }
}
