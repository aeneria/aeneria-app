<?php

declare(strict_types=1);

namespace App\GrdfAdict\Model;

/**
 * {
 *  "pce": {
 *   "id_pce": "GI123456"
 *  },
 *  "donnees_techniques": {
 *   "situation_compteur": {
 *    "numero_rue": "9",
 *    "nom_rue": "ALLEE PIERRE AUGUSTE RENOIR",
 *    "complement_adresse": "ut id esse",
 *    "code_postal": "59100",
 *    "commune": "ROUBAIX"
 *   },
 *   "caracteristiques_compteur": {
 *    "frequence": "6M",
 *    "client_sensible_mig": "Oui"
 *   },
 *   "pitd": {
 *    "identifiant_pitd": "GD0991",
 *    "libelle_pitd": "LILLE"
 *   }
 *  },
 *  "statut_restitution": {}
 * }
 *
 */
class InfoTechnique
{
    /** @var string|null */
    public $pce;

    /** @var string|null */
    public $numeroRue;

    /** @var string|null */
    public $nomRue;

    /** @var string|null */
    public $complementAdresse;

    /** @var string|null */
    public $codePostal;

    /** @var string|null */
    public $commune;

    /** @var string */
    public $rawData;

    /** @var object */
    public $rawObject;

    public static function fromJson(string $jsonData): self
    {
        $info = new self();
        $info->rawData = $jsonData;

        $data = \json_decode($jsonData);
        $info->rawObject = $data;

        $info->pce = $data->pce->id_pce;
        $data = $data->donnees_techniques->situation_compteur;

        $info->numeroRue = $data->numero_rue ?? null;
        $info->nomRue = $data->nom_rue ?? null;
        $info->complementAdresse = $data->complement_adresse ?? null;
        $info->codePostal = $data->code_postal ?? null;
        $info->commune = $data->commune ?? null;

        return $info;
    }

    public function __toString()
    {
        $parts = [];

        if ($this->numeroRue) {
            $parts[] = $this->numeroRue;
        }

        if ($this->nomRue) {
            $parts[] = $this->nomRue;
        }

        if ($this->complementAdresse) {
            $parts[] = $this->complementAdresse;
        }

        if ($this->codePostal) {
            $parts[] = $this->codePostal;
        }

        if ($this->commune) {
            $parts[] = $this->commune;
        }

        return \implode(", ", $parts);
    }
}
