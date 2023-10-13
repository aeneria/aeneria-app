<?php

declare(strict_types=1);

namespace App\GrdfAdict\Model;

/**
 * {
 *   "customer": {
 *     "customer_id": "1358019319",
 *     "usage_points": [
 *       {
 *         "usage_point": {
 *           "usage_point_id": "12345678901234",
 *           "usage_point_status": "com",
 *           "meter_type": "AMM",
 *           "usage_point_addresses": {
 *             "street": "2 bis rue du capitaine Flam",
 *             "locality": "lieudit Tourtouze",
 *             "postal_code": "32400",
 *             "insee_code": "32244",
 *             "city": "Maulichères",
 *             "country": "France",
 *             "geo_points": {
 *               "latitude": "43.687253",
 *               "longitude": "-0.087957",
 *               "altitude": "148"
 *             }
 *           }
 *         }
 *       }
 *     ]
 *   }
 * }
 *
 * We assume we only request 1 address.
 *
 */
class ContratInfo
{
    /** @var string|null */
    public $customerId;

    /** @var string|null */
    public $usagePointId;

    /** @var string|null */
    public $usagePointStatus;

    /** @var string|null */
    public $meterType;

    /** @var string|null */
    public $street;

    /** @var string|null */
    public $locality;

    /** @var string|null */
    public $postalCode;

    /** @var string|null */
    public $inseeCode;

    /** @var string|null */
    public $city;

    /** @var string|null */
    public $country;

    /** @var float|null */
    public $latitude;

    /** @var float|null */
    public $longitude;

    /** @var float|null */
    public $altitude;

    /** @var string */
    public $rawData;

    /** @var object */
    public $rawObject;

    public static function fromJson(string $jsonData): self
    {
        $address = new self();
        $address->rawData = $jsonData;

        try {
            $data = \json_decode($jsonData);
            $address->rawObject = $data;

            $data = $data->customer;
            $address->customerId = $data->customer_id;

            $usagePointData = $data->usage_points[0]->usage_point ?? null;
            $address->usagePointId = \trim($usagePointData->usage_point_id ?? null);
            $address->usagePointStatus = $usagePointData->usage_point_status ?? null;
            $address->meterType = $usagePointData->meter_type ?? null;
            if (isset($usagePointData->usage_point_addresses) && ($usagePointAddresses = $usagePointData->usage_point_addresses)) {
                $address->street = $usagePointAddresses->street ?? null;
                $address->locality = $usagePointAddresses->locality ?? null;
                $address->postalCode = $usagePointAddresses->postal_code ?? null;
                $address->inseeCode = $usagePointAddresses->insee_code ?? null;
                $address->city = $usagePointAddresses->city ?? null;
                $address->country = $usagePointAddresses->country ?? null;
                $address->latitude = \floatval($usagePointAddresses->geo_points->latitude ?? null);
                $address->longitude = \floatval($usagePointAddresses->geo_points->longitude ?? null);
                $address->altitude = \floatval($usagePointAddresses->geo_points->altitude ?? null);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(\sprintf(
                "La conversion vers l'objet Address a échoué : %s",
                $e->getMessage()
            ));
        }

        return $address;
    }

    public function __toString()
    {
        $parts = [];

        if ($this->street) {
            $parts[] = $this->street;
        }

        if ($this->locality) {
            $parts[] = $this->locality;
        }

        if ($this->postalCode) {
            $parts[] = $this->postalCode;
        }

        if ($this->city) {
            $parts[] = $this->city;
        }

        if ($this->country) {
            $parts[] = $this->country;
        }

        return \implode(", ", $parts);
    }
}
