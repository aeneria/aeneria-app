<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\MeteringData;

class MockConsommationClient extends AbstractApiClient implements ConsommationClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function requestConsoInformative(string $accessToken, string $pce, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData
    {
        $json = '{"meter_reading": {';
        $json .= '"usage_point_id": "' . $pce . '", ';
        $json .= '"start": "' . $start->format('Y-m-d') . '", ';
        $json .= '"end": "' . $start->format('Y-m-d') . '", ';
        $json .= '"quality": "BRUT", ';
        $json .= '"reading_type": {"measurement_kind": "power", "unit": "W", "aggregate": "average"}, ';
        $json .= '"interval_reading": [';

        $currentDatetime = \DateTime::createFromInterface($start);
        $data = [];
        while ($currentDatetime < $end) {
            $data[] = '{"value": "100", "date": "' . $currentDatetime->format('Y-m-d H:i:s') . '", "interval_length": "PT30M", "measure_type": "B"}';
            $currentDatetime->add(new \DateInterval('PT30M'));
        }
        $json .= \implode(', ', $data);

        $json .= ']}}';

        return MeteringData::fromJson($json);
    }
}
