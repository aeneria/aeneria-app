<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Place;
use App\Entity\User;
use App\Repository\PlaceRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractAppController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var PlaceRepository */
    protected $placeRepository;

    /** @var bool */
    protected $userCanSharePlace;

    /** @var bool */
    protected $placeCanBePublic;

    /** @var bool */
    protected $isDemoMode;

    public function __construct(bool $userCanSharePlace, bool $placeCanBePublic, bool $isDemoMode, PlaceRepository $placeRepository)
    {
        $this->placeRepository = $placeRepository;
        $this->userCanSharePlace = $userCanSharePlace;
        $this->placeCanBePublic = $placeCanBePublic;
        $this->isDemoMode = $isDemoMode;
    }

    final protected function checkPlace(int|string $placeId): Place
    {
        if (!$place = $this->placeRepository->find((int) $placeId)) {
            throw new NotFoundHttpException("L'adresse cherchée n'existe pas !");
        }

        $user = $this->getUser();
        \assert($user instanceof User);

        if (!$user->canEdit($place) || $this->isDemoMode) {
            throw new AccessDeniedHttpException("Vous n'êtes pas authorisé à modifier cette adresse.");
        }

        return $place;
    }

    final protected function canSeePlace(string $placeId): Place
    {
        if (!$place = $this->placeRepository->find($placeId)) {
            throw new NotFoundHttpException("L'adresse cherchée n'existe pas !");
        }

        $user = $this->getUser();
        \assert($user instanceof User);

        if (!$user->canSee($place, $this->userCanSharePlace, $this->placeCanBePublic)) {
            throw new AccessDeniedHttpException("Vous n'êtes pas authorisé à voir les données de cette adresse.");
        }

        return $place;
    }

    final protected function checkUser(): ?User
    {
        if (!($user = $this->getUser()) || $this->isDemoMode) {
            throw new AccessDeniedHttpException("Vous n'êtes pas authorisé à modifier le compte utilisateur.");
        }

        \assert($user instanceof User);
        return $user;
    }

    final protected function dataValidationErrorResponse(string $dataKey, string $message): JsonResponse
    {
        return new JsonResponse(\json_encode([
            'error' => [
                'dataKey' => $dataKey,
                'message' => $message,
            ],
        ]), 400);
    }
}
