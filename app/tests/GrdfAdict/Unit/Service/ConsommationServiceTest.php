<?php

declare(strict_types=1);

namespace App\GrdfAdict\Tests\Unit;

use App\GrdfAdict\Model\MeteringData;
use App\GrdfAdict\Client\ConsommationClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ConsommationServiceTest extends TestCase
{
    public function testRequestConsoInformative()
    {
        $json = <<<JSON
        {
          "consommation": {
            "journee_gaziere": "2019-05-06",
            "energie": "540"
          }
        }
        JSON;
        $data = MeteringData::fromJson($json);

        $httpClient = new MockHttpClient(
            new MockResponse($json)
        );

        $service = new ConsommationClient(
            $httpClient,
            'http://endpoint.fr'
        );

        $dataFromService = $service->requestConsoInformative(
            'accessToken',
            'pce',
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        self::assertEquals($data, $dataFromService);
    }
}
