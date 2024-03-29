<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Place;
use App\Entity\User;
use App\Repository\DataValueRepository;
use App\Repository\UserRepository;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApiConfigController extends AbstractAppController
{
    /**
     * Obtenir la configuration globale de l'application.
     */
    public function getConfiguration(ContainerBagInterface $parameters): JsonResponse
    {
        return new JsonResponse([
            'userMaxPlaces' => $parameters->get('aeneria.user.max_places'),
            'userCanSharePlace' => $parameters->get('aeneria.user.can_share_place'),
            'userCanFetch' => $parameters->get('aeneria.user.can_fetch'),
            'userCanExport' => $parameters->get('aeneria.user.can_export'),
            'userCanImport' => $parameters->get('aeneria.user.can_import'),
            'placeCanBePublic' => $parameters->get('aeneria.place_can_be_public'),
            'proxyForEnedis' => $parameters->get('aeneria.proxy_for_enedis'),
            'proxyForGrdf' => $parameters->get('aeneria.proxy_for_grdf'),
            'proxyUrl' => $parameters->get('aeneria.proxy_url'),
            'isDemoMode' => $parameters->get('aeneria.demo_mode'),
            'welcomeMessage' => $parameters->get('aeneria.welcome_message'),
            'version' => $parameters->get('aeneria.version'),
        ], 200);
    }

    /**
     * Obtenir des informations sur l'utilisateur courant.
     */
    public function getUserData(): JsonResponse
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        return new JsonResponse($user->jsonSerialize(), 200);
    }

    public function updatePassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $user = $this->checkUser();
        $data = \json_decode($request->getContent());

        if (!$data->oldPassword || !$passwordHasher->isPasswordValid($user, $data->oldPassword)) {
            return $this->dataValidationErrorResponse('oldPassword', "Le mot de passe renseigné ne correspond pas au mot de passe actuelle.");
        }
        if (!$data->newPassword) {
            return $this->dataValidationErrorResponse('newPassword', "Vous devez fournir un nouveau mot de passe 'newPassword'.");
        }

        if ($data->newPassword !== $data->newPassword2) {
            return $this->dataValidationErrorResponse('newPassword2', "Les 2 mots de passe ne sont pas identiques.");
        }

        $user
            ->setPassword($passwordHasher->hashPassword($user, $data->newPassword))
            ->setUpdatedAt(new \DateTimeImmutable())
        ;

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse('', 200);
    }

    public function updateEmail(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->checkUser();
        $data = \json_decode($request->getContent());

        if (!$data->newEmail) {
            return $this->dataValidationErrorResponse('newEmail', "Vous devez fournir un nouveau email.");
        }

        $user
            ->setUsername($data->newEmail)
            ->setUpdatedAt(new \DateTimeImmutable())
        ;

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse('', 200);
    }

    public function deleteAccount(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->checkUser();

        $data = $request->request->all();

        // Vérifier le mot de passe
        if (!$data['password'] || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->dataValidationErrorResponse('password', "Mot de passe invalide.");
        }

        if (!$data['yes-i-am-sure']) {
            return $this->dataValidationErrorResponse('yes-i-am-sure', "La case de sécurité n'a pas été fournit.");
        }

        // Vérifier qu'il y aura toujours un admin après la suppression
        $username = $user->getUsername();
        if ($userRepository->isLastAdmin($username)) {
            return $this->dataValidationErrorResponse('none', "Vous ne pouvez pas supprimer votre compte, vous êtes le seul administrateur !");
        }

        $userRepository->purge($user);

        return $this->redirectToRoute('security.login');
    }

    /**
     * Obtenir la liste des utilisateurs.
     */
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        return new JsonResponse(\json_encode(
            User::toOptionList($userRepository->list(0, -1))
        ), 200);
    }

    /**
     * Obtenir les places authorisées pour l'utilisateur courant.
     */
    public function getPlaces(DataValueRepository $dataValueRepository): JsonResponse
    {
        $ret = [];

        $user = $this->getUser();
        \assert($user instanceof User);

        if ($result = $this->placeRepository->getAllowedPlaces($user)) {
            foreach ($result as $place) {
                \assert($place instanceof Place);

                if ($periode = $dataValueRepository->getPeriodDataAmplitude($place)) {
                    $place->setPeriodeAmplitude(
                        $periode[1] ? new \DateTimeImmutable($periode[1]) : null,
                        $periode[2] ? new \DateTimeImmutable($periode[2]) : null
                    );
                }
                $ret[] = $place;
            }
        }

        return new JsonResponse(\json_encode($ret), 200);
    }

    /**
     * Obtenir les notifications de l'utilisateur courant.
     */
    public function getNotifications(NotificationService $notificationService): JsonResponse
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        $result = $notificationService->getAndDeleteNotificationFor($user);

        return new JsonResponse($result, 200);
    }
}
