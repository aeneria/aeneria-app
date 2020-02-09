<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractAppController extends AbstractController
{
    protected $placeRepository;

    public function __construct(PlaceRepository $placeRepository)
    {
        $this->placeRepository = $placeRepository;
    }
    final protected function checkPlace(string $placeId): Place
    {
        if (!$place = $this->placeRepository->find($placeId)) {
            throw new NotFoundHttpException("L'adresse cherchée n'existe pas !");
        }

        if (!$this->getUser()->canEdit($place)) {
            throw new AccessDeniedException("Vous n'êtes pas authorisé à voir les données de cette adresse.");
        }

        return $place;
    }
}
