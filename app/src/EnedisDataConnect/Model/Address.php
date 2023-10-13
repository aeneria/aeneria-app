<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Model;

/**
 * A representation of a DataConnect Token received from Data Connect API
 *
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
 * @see https://datahub-enedis.fr/data-connect/documentation/customers/
 */
class Address
{
    /** @var string|null */
    private $customerId;

    /** @var string|null */
    private $usagePointId;

    /** @var string|null */
    private $usagePointStatus;

    /** @var string|null */
    private $meterType;

    /** @var string|null */
    private $street;

    /** @var string|null */
    private $locality;

    /** @var string|null */
    private $postalCode;

    /** @var string|null */
    private $inseeCode;

    /** @var string|null */
    private $city;

    /** @var string|null */
    private $country;

    /** @var float|null */
    private $latitude;

    /** @var float|null */
    private $longitude;

    /** @var float|null */
    private $altitude;

    public static function fromJson(string $jsonData): self
    {
        $address = new self();

        try {
            $data = \json_decode($jsonData);
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

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): self
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getUsagePointId(): ?string
    {
        return $this->usagePointId;
    }

    public function setUsagePointId(string $usagePointId): self
    {
        $this->usagePointId = $usagePointId;

        return $this;
    }

    public function getUsagePointStatus(): ?string
    {
        return $this->usagePointStatus;
    }

    public function setUsagePointStatus(string $usagePointStatus): self
    {
        $this->usagePointStatus = $usagePointStatus;

        return $this;
    }

    public function getMeterType(): ?string
    {
        return $this->meterType;
    }

    public function setMeterType(?string $meterType): self
    {
        $this->meterType = $meterType;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getInseeCode(): ?string
    {
        return $this->inseeCode;
    }

    public function setInseeCode(?string $inseeCode): self
    {
        $this->inseeCode = $inseeCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getAltitude(): ?float
    {
        return $this->altitude;
    }

    public function setAltitude(?float $altitude): self
    {
        $this->altitude = $altitude;

        return $this;
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
