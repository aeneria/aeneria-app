<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\InfoTechnique;

class MockContratClient extends AbstractApiClient implements ContratClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function requestInfoTechnique(string $accessToken, string $pce): InfoTechnique
    {
        $json = <<<JSON
        {
          "pce": {
            "id_pce": "{$pce}"
          },
          "donnees_techniques": {
            "situation_compteur": {
            "numero_rue": "9",
            "nom_rue": "ALLEE PIERRE AUGUSTE RENOIR",
            "complement_adresse": "ut id esse",
            "code_postal": "59100",
            "commune": "ROUBAIX"
            },
            "caracteristiques_compteur": {
            "frequence": "6M",
            "client_sensible_mig": "Oui"
            },
            "pitd": {
            "identifiant_pitd": "GD0991",
            "libelle_pitd": "LILLE"
            }
          },
          "statut_restitution": {}
        }
        JSON;

        return InfoTechnique::fromJson($json);
    }
}
