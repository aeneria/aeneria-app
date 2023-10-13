<?php

declare(strict_types=1);

namespace App\GrdfAdict\Client;

use App\GrdfAdict\Model\InfoTechnique;

/**
 * Implements DataConnect Customers API
 */
interface ContratClientInterface
{
    public function requestInfoTechnique(string $accessToken, string $pce): InfoTechnique;
}
