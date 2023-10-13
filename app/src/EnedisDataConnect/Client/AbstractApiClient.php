<?php

declare(strict_types=1);

namespace App\EnedisDataConnect\Client;

use App\EnedisDataConnect\Exception\DataConnectConsentException;
use App\EnedisDataConnect\Exception\DataConnectDataNotFoundException;
use App\EnedisDataConnect\Exception\DataConnectException;
use App\EnedisDataConnect\Exception\DataConnectQuotaExceededException;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractApiClient
{
    protected function checkResponse(ResponseInterface $response): void
    {
        $code = $response->getStatusCode();

        if (200 !== $code) {
            switch ($code) {
                case 403:
                    throw new DataConnectConsentException($response->getContent(false), $code);
                case 404:
                    throw new DataConnectDataNotFoundException($response->getContent(false), $code);
                case 429:
                    throw new DataConnectQuotaExceededException($response->getContent(false), $code);
                default:
                    throw new DataConnectException($response->getContent(false), $code);
            }
        }
    }
}
