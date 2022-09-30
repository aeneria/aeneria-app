<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ApiConfigController extends AbstractAppController
{
    /**
     * Obtenir la configuration globale de l'application.
     */
    public function getConfiguration(ContainerBagInterface $parameters): JsonResponse {
        return new JsonResponse([
            'userMaxPlaces' => $parameters->get('aeneria.user.max_places'),
            'userCanSharePlace' => $parameters->get('aeneria.user.can_share_place'),
            'userCanFetch' => $parameters->get('aeneria.user.can_fetch'),
            'userCanExport' => $parameters->get('aeneria.user.can_export'),
            'userCanImport' => $parameters->get('aeneria.user.can_import'),
            'placeCanBePublic' => $parameters->get('aeneria.place_can_be_public'),
            'isDemoMode' => $parameters->get('aeneria.demo_mode'),
            'welcomeMessage' => $parameters->get('aeneria.welcome_message'),
            'version' => $parameters->get('aeneria.version'),
        ], 200);
    }

    /**
     * Obtenir des informations sur l'utilisateur courant.
     */
    public function getUserData(): JsonResponse {
        return new JsonResponse($this->getUser()->jsonSerialize(), 200);
    }

    public function updatePassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder
    ):JsonResponse {
        $user = $this->checkUser();
        $data = \json_decode($request->getContent());

        if (!$data->oldPassword || !$passwordEncoder->isPasswordValid($user, $data->oldPassword)) {
            return new JsonResponse("Le mot de passe renseigné ne correspond pas au mot de passe actuelle.", 403);
        }
        if (!$data->newPassword) {
            return new JsonResponse("Vous devez fournir un nouveau mot de passe 'newPassword'.", 412);
        }

        if ($data->newPassword !== $data->newPassword2) {
            return new JsonResponse("Les 2 mots de passe ne sont pas identiques.", 412);
        }


        $user->setPassword($passwordEncoder->encodePassword($user, $data->newPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse('', 200);
    }

    public function updateEmail(Request $request, EntityManagerInterface $entityManager):JsonResponse
    {
        $user = $this->checkUser();
        $data = \json_decode($request->getContent());

        if (!$data->newEmail) {
            return new JsonResponse("Vous devez fournir un nouveau email.", 412);
        }

        $user->setUsername($data->newEmail);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse('', 200);
    }

    public function deleteAccount(
        Request $request,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $user = $this->checkUser();

        $data = $request->request->all();

        // Vérifier le mot de passe
        if (!$data['password'] || !$passwordEncoder->isPasswordValid($user, $data['password'])) {
            return new JsonResponse("Mot de passe invalide.", 403);
        }

        if (!$data['yes-i-am-sure']) {
            throw new \DomainException("La case de sécurité n'a pas été fournit.");
        }

        // Vérifier qu'il y aura toujours un admin après la suppression
        $username = $user->getUsername();
        if ($this->userRepository->isLastAdmin($username)) {
            throw new \DomainException("Vous ne pouvez pas supprimer votre compte, vous êtes le seul administrateur !");
        }

        $userRepository->purge($user);

        $this->addFlash('success', "Votre compte a bien été supprimé. À bientôt.");

        $this->redirectToRoute('security.login');
    }

    /**
     * Obtenir les places authorisées pour l'utilisateur courant.
     */
    public function getPlaces(): JsonResponse
    {
        $result = $this->placeRepository->getAllowedPlaces($this->getUser());

        return new JsonResponse($result, 200);
    }

    /**
     * Obtenir les notifications de l'utilisateur courant.
     */
    public function getNotifications(NotificationService $notificationService): JsonResponse
    {
        $result = $notificationService->getAndDeleteNotificationFor($this->getUser());

        return new JsonResponse($result, 200);
    }
}
