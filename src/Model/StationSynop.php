<?php

namespace App\Model;

class StationSynop
{
  /** @var int */
    public $key;
    /** @var string */
    public $label;
    /** @var float */
    public $latitude;
    /** @var float */
    public $longitude;
    /** @var float */
    public $altitude;

    public function __construct(
      int $key,
      string $label,
      string $latitude,
      string $longitude,
      string $altitude
    ) {
      $this->key = $key;
      $this->label = $label;
      $this->latitude = $latitude;
      $this->longitude = $longitude;
      $this->altitude = $altitude;
    }
}
