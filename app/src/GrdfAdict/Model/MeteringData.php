<?php

declare(strict_types=1);

namespace App\GrdfAdict\Model;

use App\GrdfAdict\Exception\GrdfAdictDataNotFoundException;

class MeteringData
{
    /** @var \DateTimeImmutable */
    public $date;

    /** @var float */
    public $value;

    /** @var string */
    public $rawData;

    /** @var object */
    public $rawObject;

    public static function fromJson(string $jsonData): self
    {
        $meteringData = new self();
        $meteringData->rawData = $jsonData;

        $data = \json_decode($jsonData);
        $meteringData->rawObject = $data;

        if ($data->consommation) {
            $meteringData->date = \DateTimeImmutable::createFromFormat('Y-m-d', $data->consommation->journee_gaziere);
            $meteringData->value = $data->consommation->energie;
        } else {
            throw new GrdfAdictDataNotFoundException($data->statut_restitution->message ?? null);
        }

        return $meteringData;
    }
}
