<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiConfigController extends AbstractAppController
{
    /**
     * Obtenir les places authorisées pour l'utilisateur courant.
     */
    public function getPlacesAction(): JsonResponse
    {
        $result = $this->placeRepository->getAllowedPlaces($this->getUser());

        return new JsonResponse($result, 200);
    }
}
