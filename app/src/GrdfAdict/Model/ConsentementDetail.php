<?php

declare(strict_types=1);

namespace App\GrdfAdict\Model;

/**
 * A representation of a consentement detail received from GRDF adict API
 *
 * {
 *   "at_hash": "OU6ozMPhcBM8zA_h-fnngA",
 *   "sub": "l5ws745nvksl7lrb7l097bv3xi7kjtypy80fmsf19wyko8ocu0ocgf2viddb3udzb4hoq0tgyi9",
 *   "auditTrackingId": "f1b233cf-510b-4628-81ca-bcbd718a63af-4170638",
 *   "iss": "https://sofit-sso-oidc.grdf.fr:443/openam/oauth2/externeGrdf",
 *   "tokenName": "id_token",
 *   "aud": "grdf_login",
 *   "c_hash": "8CHKLYE9j2o07wkAIBi9WQ",
 *   "acr": "0",
 *   "org.forgerock.openidconnect.ops": "QdEfIVHM9Tnjcj0UqWb7rsvWfLo",
 *   "azp": "grdf_login",
 *   "auth_time": 1617971537,
 *   "realm": "/externeGrdf",
 *   "consentements": "[{\"pce\":\"14207380494605\",\"id_accreditation\":\"0f06666b-9f78-453f-983b-c21a12a62228\"}]",
 *   "exp": 1617976074,
 *   "tokenType": "JWTToken",
 *   "iat": 1617972474
 * }
 *
 * @see https://site.grdf.fr/web/grdf-adict/technique/
 */
class ConsentementDetail
{
    /** @var string */
    public $pce;

    /** @var string */
    public $idAccreditation;

    /** @var string */
    public $rawData;

    /** @var object */
    public $rawObject;

    /**
     * Extract info from accessToken
     *
     * {
     *   "access_token": "XXXXXXXXXXXXXXXXXXXXXX",
     *   "scope": "ZZZZZZZZZZ",
     *   "id_token": "YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY",
     *   "token_type": "Bearer",
     *   "expires_in": 14399
     * }
     *
     * id_token need then to be decoded to get something like :
     *
     * {
     *   "at_hash": "OU6ozMPhcBM8zA_h-fnngA",
     *   "sub": "l5ws745nvksl7lrb7l097bv3xi7kjtypy80fmsf19wyko8ocu0ocgf2viddb3udzb4hoq0tgyi9",
     *   "auditTrackingId": "f1b233cf-510b-4628-81ca-bcbd718a63af-4170638",
     *   "iss": "https://sofit-sso-oidc.grdf.fr:443/openam/oauth2/externeGrdf",
     *   "tokenName": "id_token",
     *   "aud": "grdf_login",
     *   "c_hash": "8CHKLYE9j2o07wkAIBi9WQ",
     *   "acr": "0",
     *   "org.forgerock.openidconnect.ops": "QdEfIVHM9Tnjcj0UqWb7rsvWfLo",
     *   "azp": "grdf_login",
     *   "auth_time": 1617971537,
     *   "realm": "/externeGrdf",
     *   "consentements": "[{\"pce\":\"14207380494605\",\"id_accreditation\":\"0f06666b-9f78-453f-983b-c21a12a62228\"}]",
     *   "exp": 1617976074,
     *   "tokenType": "JWTToken",
     *   "iat": 1617972474
     * }
     */
    public static function fromJson(string $jsonData): self
    {
        $consentement = new self();
        $consentement->rawData = $jsonData;

        try {
            $data = \json_decode($jsonData);
            $consentement->rawObject = $data;

            $idToken = \trim($data->id_token);
            $idToken = \explode('.', $idToken);

            list($headb64, $bodyb64, $cryptob64) = $idToken;
            $decodedData = \json_decode(\base64_decode($bodyb64));
            $consentements = \json_decode($decodedData->consentements);

            $consentement->pce = $consentements[0]->pce;
            $consentement->idAccreditation = $consentements[0]->id_accreditation;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(\sprintf(
                "La conversion vers l'objet ConsentementDetail a échoué : %s",
                $e->getMessage()
            ));
        }

        return $consentement;
    }
}
