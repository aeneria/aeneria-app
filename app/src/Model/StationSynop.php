<?php

declare(strict_types=1);

namespace App\Model;

class StationSynop
{
    public int $key;
    public string $label;
    public float $latitude;
    public float $longitude;
    public float $altitude;

    public function __construct(
        int $key,
        string $label,
        string $latitude,
        string $longitude,
        string $altitude
    ) {
        $this->key = $key;
        $this->label = $label;
        $this->latitude = (float) $latitude;
        $this->longitude = (float) $longitude;
        $this->altitude = (float) $altitude;
    }
}
