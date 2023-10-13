<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Exception\GrdfAdictConsentException;
use App\GrdfAdict\Exception\GrdfAdictDataNotFoundException;
use App\GrdfAdict\Exception\GrdfAdictException;
use App\GrdfAdict\Exception\GrdfAdictQuotaExceededException;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractApiClient
{
    protected function checkResponse(ResponseInterface $response): void
    {
        $statutCode = $response->getStatusCode();

        $code = null;
        $message = null;
        try {
            $data = \json_decode($response->getContent());

            // S'il y a un élément statut_restitution dans la réponse,
            // c'est que l'appel est en échec
            if (isset($data->statut_restitution)) {
                $statutCode = $statutCode === 200 ? 500 : $statutCode;
                $code = $data->statut_restitution->code;
                $message = $data->statut_restitution->message;
            }
        } catch (\Exception $e) {
            $code = $response->getStatusCode();
            $message = $response->getContent(false);
        }

        if ($statutCode !== 200) {
            switch ($response->getStatusCode()) {
                case 403:
                    throw new GrdfAdictConsentException($message, $code);
                case 404:
                    throw new GrdfAdictDataNotFoundException($message, $code);
                case 429:
                    throw new GrdfAdictQuotaExceededException($message, $code);
                default:
                    throw new GrdfAdictException($message, $code);
            }
        }
    }
}
