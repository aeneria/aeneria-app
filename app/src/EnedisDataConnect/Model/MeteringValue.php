<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Model;

/**
 * A representation of a Result received from Data Connect API
 *
 * {
 *    "value": "540",
 *    "date": "2019-05-06"
 * }
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/metering-data-v4/
 */
class MeteringValue
{
    /** @var float */
    private $value;

    /** @var \DateTimeImmutable */
    private $date;

    /** @var \DateInterval */
    private $intervalLength;

    public static function fromStdClass(\stdClass $data): self
    {
        $meteringValue = new self();

        try {
            $meteringValue->value = \floatval($data->value);
            $meteringValue->date = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $data->date) ?: \DateTimeImmutable::createFromFormat('!Y-m-d', $data->date);
            $meteringValue->intervalLength = $data->interval_length ? new \DateInterval($data->interval_length) : null;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(\sprintf(
                "La conversion vers l'objet MeteringValue a échoué : %s",
                $e->getMessage()
            ));
        }

        return $meteringValue;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(?float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getIntervalLength(): ?\DateInterval
    {
        return $this->intervalLength;
    }

    public function setIntervalLength(?\DateInterval $intervalLength): self
    {
        $this->intervalLength = $intervalLength;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }
}
