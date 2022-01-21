<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ApiConfigController extends AbstractAppController
{
    /**
     * Obtenir la configuration globale de l'application.
     */
    public function getConfigurationAction(
        int $userMaxPlaces,
        bool $userCanSharePlace,
        bool $userCanFetch,
        bool $userCanExport,
        bool $userCanImport,
        bool $placeCanBePublic,
        bool $isDemoMode
    ): JsonResponse {
        return new JsonResponse([
            'userMaxPlaces' => $userMaxPlaces,
            'userCanSharePlace' => $userCanSharePlace,
            'userCanFetch' => $userCanFetch,
            'userCanExport' => $userCanExport,
            'userCanImport' => $userCanImport,
            'placeCanBePublic' => $placeCanBePublic,
            'isDemoMode' => $isDemoMode,
        ], 200);
    }

    /**
     * Obtenir des infromations sur l'utilisateur courant.
     */
    public function getUserAction(): JsonResponse {
        return new JsonResponse($this->getUser()->jsonSerialize(), 200);
    }

    public function updatePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder):JsonResponse
    {
        $user = $this->checkUser();
        $data = \json_decode($request->getContent());

        if (!$passwordEncoder->isPasswordValid($user, $data->oldPassword)) {
            return new JsonResponse("Le mot de passe renseigné ne correspond pas au mot de passe actuelle.", 403);
        }
        if (!$data->newPassword) {
            return new JsonResponse("Vous devez fournir un nouveau mot de passe.", 412);
        }

        if ($data->newPassword !== $data->newPassword2) {
            return new JsonResponse("Les 2 mots de passe ne sont pas identiques.", 412);
        }


        $user->setPassword($passwordEncoder->encodePassword($user, $data->newPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse('', 200);
    }

    /**
     * Obtenir les places authorisées pour l'utilisateur courant.
     */
    public function getPlacesAction(): JsonResponse
    {
        $result = $this->placeRepository->getAllowedPlaces($this->getUser());

        return new JsonResponse($result, 200);
    }
}
