<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Model;

/**
 * A representation of a Result received from Data Connect API
 *
 * {
 *   "meter_reading": {
 *     "usage_point_id": "16401220101758",
 *     "start": "2019-05-06",
 *     "end": "2019-05-12",
 *     "quality": "BRUT",
 *     "reading_type": {
 *       "measurement_kind": "energy",
 *       "measuring_period": "P1D",
 *       "unit": "Wh",
 *       "aggregate": "sum"
 *     },
 *     "interval_reading": [
 *       {
 *         "value": "540",
 *         "date": "2019-05-06"
 *       }
 *     ]
 *   }
 * }
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/metering-data-v4/
 */
class MeteringData
{
    public const TYPE_CONSUMPTION_LOAD_CURVE = 'CONSUMPTION_LOAD_CURVE';
    public const TYPE_PRODUCTION_LOAD_CURVE = 'PRODUCTION_LOAD_CURVE';
    public const TYPE_DAILY_CONSUMPTION = 'DAILY_CONSUMPTION';
    public const TYPE_DAILY_PRODUCTION = 'DAILY_PRODUCTION';

    /** @var string */
    private $usagePointId;

    /** @var \DateTimeImmutable */
    private $start;

    /** @var \DateTimeImmutable */
    private $end;

    /** @var string */
    private $unit;

    /** @var string */
    private $dataType;

    /** @var MeteringValue[] */
    private $values = [];

    public static function fromJson(string $jsonData, string $dataType): self
    {
        $meteringData = new self();

        try {
            $data = \json_decode($jsonData);

            $meteringData->dataType = $dataType;
            $meteringData->usagePointId = $data->meter_reading->usage_point_id;
            $meteringData->start = \DateTimeImmutable::createFromFormat('!Y-m-d', $data->meter_reading->start);
            $meteringData->end = \DateTimeImmutable::createFromFormat('!Y-m-d', $data->meter_reading->end);
            $meteringData->unit = $data->meter_reading->reading_type->unit;

            // Les données journalière on une info de péride de mesure, pour la courbe de charge, cette
            // info est située au niveau de chaque mesure (parce qu'elle peut varier). Pour rendre le tout
            // homogène, on déplace cette valuer au niveau de la mesure si elle existe.
            $period = $data->meter_reading->reading_type->measuring_period ?? null;

            foreach ($data->meter_reading->interval_reading as $value) {
                if ($period) {
                    $value->interval_length = $period;
                }
                $meteringData->values[] = MeteringValue::fromStdClass($value);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(\sprintf(
                "La conversion vers l'objet MeteringData a échoué : %s",
                $e->getMessage()
            ));
        }

        return $meteringData;
    }

    public function getUsagePointId(): string
    {
        return $this->usagePointId;
    }

    public function setUsagePointId(string $usagePointId): self
    {
        $this->usagePointId = $usagePointId;

        return $this;
    }

    public function getDataType(): string
    {
        return $this->dataType;
    }

    public function setDataType(string $dataType): self
    {
        $this->dataType = $dataType;

        return $this;
    }

    public function getStart(): \DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(\DateTimeImmutable $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): \DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(\DateTimeImmutable $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): self
    {
        $this->values = $values;

        return $this;
    }
}
