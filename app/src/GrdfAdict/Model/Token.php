<?php

declare(strict_types=1);

namespace App\GrdfAdict\Model;

/**
 * A representation of a token received from GRDF adict API
 *
 * {
 *   "access_token": "ba42fe5a-0eaa-11e5-9813-4dd05b3a25f3",
 *   "scope": "/adict/v2",
 *   "token_type": "Bearer",
 *   "id_token": "16401220101758",
 *   "expires_in": 12600
 * }
 *
 * @see https://site.grdf.fr/web/grdf-adict/technique/
 */
class Token
{
    /** @var string */
    public $accessToken;

    /** @var string */
    public $tokenType;

    /** @var string */
    public $scope;

    public $accessTokenExpirationDate;

    /** @var string */
    public $rawData;

    /** @var object */
    public $rawObject;

    /**
     *
     * "{"access_token":"eyJ0eXAiOiJKV1QiLCJ6aXAiOiJOT05FIiwia2lkIjoicVpOQzBPcWtuNlE0cXkrVmFVZmVlQmpNbktRPSIsImFsZyI6IlJTMjU2In0.eyJzdWIiOiJzaW1vbl9tZWxsZXJpbl9ncmRmIiwiY3RzIjoiT0FVVEgyX1NUQVRFTEVTU19HUkFOVCIsImF1ZGl0VHJhY2tpbmdJZCI6ImYxYjIzM2NmLTUxMGItNDYyOC04MWNhLWJjYmQ3MThhNjNhZi00MzUyMzIyIiwiaXNzIjoiaHR0cHM6Ly9zb2ZpdC1zc28tb2lkYy5ncmRmLmZyOjQ0My9vcGVuYW0vb2F1dGgyL2V4dGVybmVHcmRmIiwidG9rZW5OYW1lIjoiYWNjZXNzX3Rva2VuIiwidG9rZW5fdHlwZSI6IkJlYXJlciIsImF1dGhHcmFudElkIjoiTUxia05yUzVub0RmTEtmckp2a3daSFdCUGV3IiwiYXVkIjoic2ltb25fbWVsbGVyaW5fZ3JkZiIsIm5iZiI6MTYxNzk4MjM2NSwiZ3JhbnRfdHlwZSI6ImNsaWVudF9jcmVkZW50aWFscyIsInNjb3BlIjpbIi9hZGljdC92MSJdLCJhdXRoX3RpbWUiOjE2MTc5ODIzNjUsInJlYWxtIjoiL2V4dGVybmVHcmRmIiwiZXhwIjoxNjE3OTk2NzY1LCJpYXQiOjE2MTc5ODIzNjUsImV4cGlyZXNfaW4iOjE0NDAwLCJqdGkiOiJRanI4cERhTEo5T3dSNzYyTkl1bXNXWDZqaWMiLCJ1X2VtIjoia215cnhrZ3Nkd2phMmEyY25sY3dwbWF0ZGFjMXQweDFtdGpzajBqbDdlaDg5enQwcHoyYWJia3Jsc2RnYTlwM2VxNDdscTA1cnpjIiwicnMiOiJTaW1vbiBNZWxsZXJpbiIsImNsaWVudF9pZCI6InNpbW9uX21lbGxlcmluX2dyZGYifQ.nteArh952vRQeU5A"
     */
    public static function fromJson(string $jsonData): self
    {
        $token = new self();
        $token->rawData = $jsonData;

        try {
            $data = \json_decode($jsonData);
            $token->rawObject = $data;

            $token->accessToken = $data->access_token;
            $expirationDate = (new \DateTime())->add(new \DateInterval('PT' . $data->expires_in . 'S'));
            $token->accessTokenExpirationDate = \DateTimeImmutable::createFromMutable($expirationDate);
            $token->tokenType = $data->token_type;
            $token->scope = $data->scope;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(\sprintf(
                "La conversion vers l'objet Token a échoué : %s",
                $e->getMessage()
            ));
        }

        return $token;
    }

    public function isAccessTokenStillValid(): bool
    {
        return $this->accessTokenExpirationDate && ($this->accessTokenExpirationDate > new \DateTimeImmutable());
    }
}
