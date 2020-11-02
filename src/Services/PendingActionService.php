<?php

namespace App\Services;

use App\Entity\PendingAction;
use App\Entity\Place;
use App\Entity\User;
use App\Repository\PendingActionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Data exporter services.
 */
class PendingActionService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PendingActionRepository */
    private $actionRepository;

    public function __construct(EntityManagerInterface $entityManager, PendingActionRepository $actionRepository)
    {
        $this->entityManager = $entityManager;
        $this->actionRepository = $actionRepository;
    }

    public function createDataConnectCallbackAction(User $user, Place $place = null): PendingAction
    {
        $token = \bin2hex(\openssl_random_pseudo_bytes(PendingAction::TOKEN_LENGTH / 2));

        $action = (new PendingAction())
            ->setToken($token)
            ->setUser($user)
            ->setAction(PendingAction::ACTION_DATA_CONNECT_CALLBACK)
            ->setExpirationDate(new \DateTimeImmutable('now + 1 day'))
        ;

        if ($place) {
            $action->setParam(['place' => $place->getId()]);
        }

        $this->entityManager->persist($action);
        $this->entityManager->flush();

        return $action;
    }

    public function findDataConnectCallbackAction(User $user, string $token): PendingAction
    {
        if (!$action = $this->actionRepository->findOneByToken($token)) {
            throw new EntityNotFoundException('Impossible de trouver la demande correspondante');
        }
        \assert($action instanceof PendingAction);

        if ($action->getUser() !== $user) {
            throw new AccessDeniedHttpException("Le token ne correspond pas à l'utilisateur courant");
        }

        if ($action->getExpirationDate() < new \DateTimeImmutable()) {
            throw new AccessDeniedHttpException('Le token a expiré.');
        }

        return $action;
    }

    public function delete(PendingAction $action)
    {
        $this->entityManager->remove($action);
        $this->entityManager->flush();
    }
}
