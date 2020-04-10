<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractAppController extends AbstractController
{
    /** @var PlaceRepository */
    protected $placeRepository;

    /** @var bool */
    protected $userCanSharePlace;

    /** @var bool */
    protected $placeCanBePublic;

    public function __construct(bool $userCanSharePlace, bool $placeCanBePublic, PlaceRepository $placeRepository)
    {
        $this->placeRepository = $placeRepository;
        $this->userCanSharePlace = $userCanSharePlace;
        $this->placeCanBePublic = $placeCanBePublic;
    }

    final protected function checkPlace(string $placeId): Place
    {
        if (!$place = $this->placeRepository->find($placeId)) {
            throw new NotFoundHttpException("L'adresse cherchée n'existe pas !");
        }

        if (!$this->getUser()->canEdit($place)) {
            throw new AccessDeniedHttpException("Vous n'êtes pas authorisé à modifier cette adresse.");
        }

        return $place;
    }

    final protected function canSeePlace(string $placeId): Place
    {
        if (!$place = $this->placeRepository->find($placeId)) {
            throw new NotFoundHttpException("L'adresse cherchée n'existe pas !");
        }

        if (!$this->getUser()->canSee($place, $this->userCanSharePlace, $this->placeCanBePublic)) {
            throw new AccessDeniedHttpException("Vous n'êtes pas authorisé à voir les données de cette adresse.");
        }

        return $place;
    }
}
