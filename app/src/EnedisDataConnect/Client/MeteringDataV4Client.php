<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Model\MeteringData;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Implements Metering Data V4
 *
 * @see https://datahub-enedis.fr/data-connect/documentation/metering-data-v4/
 */
class MeteringDataV4Client extends AbstractApiClient implements MeteringDataV4ClientInterface
{
    /** @var HttpClientInterface */
    private $httpClient;
    /** @var string */
    private $dataEndpoint;

    public function __construct(HttpClientInterface $httpClient, string $dataEndpoint)
    {
        $this->httpClient = $httpClient;
        $this->dataEndpoint = $dataEndpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function requestConsumptionLoadCurve(string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData
    {
        return $this->requestMeteringData(
            'consumption_load_curve',
            MeteringData::TYPE_CONSUMPTION_LOAD_CURVE,
            $accessToken,
            $usagePointId,
            $start,
            $end
        );
    }

    /**
     * {@inheritdoc}
     */
    public function requestProductionLoadCurve(string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData
    {
        return $this->requestMeteringData(
            'production_load_curve',
            MeteringData::TYPE_PRODUCTION_LOAD_CURVE,
            $accessToken,
            $usagePointId,
            $start,
            $end
        );
    }

    /**
     * {@inheritdoc}
     */
    public function requestDailyConsumption(string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData
    {
        return $this->requestMeteringData(
            'daily_consumption',
            MeteringData::TYPE_DAILY_CONSUMPTION,
            $accessToken,
            $usagePointId,
            $start,
            $end
        );
    }

    /**
     * {@inheritdoc}
     */
    public function requestDailyProduction(string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData
    {
        return $this->requestMeteringData(
            'daily_production',
            MeteringData::TYPE_DAILY_PRODUCTION,
            $accessToken,
            $usagePointId,
            $start,
            $end
        );
    }

    /**
     * Request MeterinData.
     */
    private function requestMeteringData(string $endpoint, string $dataType, string $accessToken, string $usagePointId, \DateTimeInterface $start, \DateTimeInterface $end): MeteringData
    {
        $response = $this->httpClient->request(
            'GET',
            \sprintf('%s/v4/metering_data/%s', $this->dataEndpoint, $endpoint),
            [
                'headers' => [
                    'accept' => 'application/json',
                ],
                'auth_bearer' => $accessToken,
                'query' => [
                    'usage_point_id' => $usagePointId,
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                ],
            ]
        );

        $this->checkResponse($response);

        return MeteringData::fromJson($response->getContent(), $dataType);
    }
}
