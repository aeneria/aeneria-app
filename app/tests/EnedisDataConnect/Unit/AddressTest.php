<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Tests\Unit;

use App\EnedisDataConnect\Model\Address;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class AddressTest extends TestCase
{
    public function testHydratation()
    {
        $data = <<<JSON
        {
          "customer": {
            "customer_id": "1358019319",
            "usage_points": [
              {
                "usage_point": {
                  "usage_point_id": "12345678901234",
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

        $address = Address::fromJson($data);

        self::assertInstanceOf(Address::class, $address);
        self::assertSame('1358019319', $address->getCustomerId());
        self::assertSame('12345678901234', $address->getUsagePointId());
        self::assertSame('com', $address->getUsagePointStatus());
        self::assertSame('AMM', $address->getMeterType());
        self::assertSame('2 bis rue du capitaine Flam', $address->getStreet());
        self::assertSame('lieudit Tourtouze', $address->getLocality());
        self::assertSame('32400', $address->getPostalCode());
        self::assertSame('32244', $address->getInseeCode());
        self::assertSame('Maulichères', $address->getCity());
        self::assertSame('France', $address->getCountry());
        self::assertSame(43.687253, $address->getLatitude());
        self::assertSame(-0.087957, $address->getLongitude());
        self::assertSame(148.0, $address->getAltitude());
        self::assertSame("2 bis rue du capitaine Flam, lieudit Tourtouze, 32400, Maulichères, France", $address . "");
    }

    public function testSerialization()
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        $data = <<<JSON
          {
            "customer": {
              "customer_id": "1358019319",
              "usage_points": [
                {
                  "usage_point": {
                    "usage_point_id": "12345678901234",
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

        $address = Address::fromJson($data);

        $deserializedAddress = $serializer->deserialize(
            $serializer->serialize($address, 'json'),
            Address::class,
            'json'
        );

        self::assertInstanceOf(Address::class, $deserializedAddress);
        self::assertSame('1358019319', $deserializedAddress->getCustomerId());
        self::assertSame('12345678901234', $deserializedAddress->getUsagePointId());
        self::assertSame('com', $deserializedAddress->getUsagePointStatus());
        self::assertSame('AMM', $deserializedAddress->getMeterType());
        self::assertSame('2 bis rue du capitaine Flam', $deserializedAddress->getStreet());
        self::assertSame('lieudit Tourtouze', $deserializedAddress->getLocality());
        self::assertSame('32400', $deserializedAddress->getPostalCode());
        self::assertSame('32244', $deserializedAddress->getInseeCode());
        self::assertSame('Maulichères', $deserializedAddress->getCity());
        self::assertSame('France', $deserializedAddress->getCountry());
        self::assertSame(43.687253, $deserializedAddress->getLatitude());
        self::assertSame(-0.087957, $deserializedAddress->getLongitude());
        self::assertSame(148.0, $deserializedAddress->getAltitude());
        self::assertSame("2 bis rue du capitaine Flam, lieudit Tourtouze, 32400, Maulichères, France", $address . "");
    }
}
