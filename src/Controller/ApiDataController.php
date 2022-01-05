<?php

namespace App\Controller;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\PlaceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiDataController extends AbstractAppController
{
    /** @var FeedDataRepository */
    private $feedDataRepository;

    /** @var DataValueRepository */
    private $dataValueRepository;

    public function __construct(bool $userCanSharePlace, bool $placeCanBePublic, PlaceRepository $placeRepository, bool $isDemoMode, FeedDataRepository $feedDataRepository, DataValueRepository $dataValueRepository)
    {
        parent::__construct($userCanSharePlace, $placeCanBePublic, $isDemoMode, $placeRepository);

        $this->feedDataRepository = $feedDataRepository;
        $this->dataValueRepository = $dataValueRepository;
    }

    /**
     * Obtenir des points de mesure pour un FeeData.
     */
    public function getPointAction(string $feedDataId, string $frequence, string $debut, string $fin): JsonResponse
    {
        $feedData = $this->canSeeFeedData($feedDataId);

        $frequence = DataValue::getFrequencyFromMachineName(\strtoupper($frequence));
        $debut = DataValue::adaptToFrequency(new \DateTimeImmutable($debut), $frequence);
        $fin = new \DateTimeImmutable($fin . ' 23:59:59');

        $result = $this->dataValueRepository->getValue($debut, $fin, $feedData, $frequence);

        return new JsonResponse(\json_encode($result), 200);
    }

    /**
     * Obtenir points de mesure agrégés selon une colonne de la table dataValue.
     */
    public function getRepartitionAction(string $feedDataId, string $frequence, string $colonne, string $debut, string $fin): JsonResponse
    {
        $feedData = $this->canSeeFeedData($feedDataId);

        $frequence = DataValue::getFrequencyFromMachineName(\strtoupper($frequence));
        $debut = DataValue::adaptToFrequency(new \DateTimeImmutable($debut), $frequence);
        $fin = new \DateTimeImmutable($fin . ' 23:59:59');

        $result = $this->dataValueRepository->getAvgValueGroupBy($debut, $fin, $feedData, $frequence, $colonne);

        return new JsonResponse(\json_encode($result), 200);
    }

    /**
     * Obtenir points de mesure agrégés selon 2 colonnes de la table dataValue.
     */
    public function getDoubleRepartitionAction(string $feedDataId, string $frequence, string $colonneX, string $colonneY, string $debut, string $fin): JsonResponse
    {
        $feedData = $this->canSeeFeedData($feedDataId);

        $frequence = DataValue::getFrequencyFromMachineName(\strtoupper($frequence));
        $debut = DataValue::adaptToFrequency(new \DateTimeImmutable($debut), $frequence);
        $fin = new \DateTimeImmutable($fin . ' 23:59:59');

        $result = $this->dataValueRepository->getRepartitionValue($debut, $fin, $feedData, $colonneX, $colonneY, $frequence);

        return new JsonResponse(\json_encode($result), 200);
    }

    /**
     * Obtenir la somme de points de mesure.
     */
    public function getSommeAction(string $feedDataId, string $frequence, string $debut, string $fin): JsonResponse
    {
        $feedData = $this->canSeeFeedData($feedDataId);

        $frequence = DataValue::getFrequencyFromMachineName(\strtoupper($frequence));
        $debut = DataValue::adaptToFrequency(new \DateTimeImmutable($debut), $frequence);
        $fin = new \DateTimeImmutable($fin . ' 23:59:59');

        // Get data between $debut & $fin for requested frequency.
        $result = $this->dataValueRepository->getSumValue($debut, $fin, $feedData, $frequence);

        return new JsonResponse(\json_encode($result), 200);
    }

    /**
     * Obtenir le nombre de jours/mois/... où la mesure demandée a été inférieure à une valeur.
     */
    public function getNombreInferieurAction(string $feedDataId, string $valeur, string $frequence, string $debut, string $fin): JsonResponse
    {
        $feedData = $this->canSeeFeedData($feedDataId);

        $frequence = DataValue::getFrequencyFromMachineName(\strtoupper($frequence));
        $debut = DataValue::adaptToFrequency(new \DateTimeImmutable($debut), $frequence);
        $fin = new \DateTimeImmutable($fin . ' 23:59:59');

        // Get data between $debut & $fin for requested frequency.
        $result = $this->dataValueRepository->getNumberInfValue($debut, $fin, $feedData, $frequence, $valeur);

        return new JsonResponse(\json_encode($result), 200);
    }

    /**
     * Obtenir le croisement de 2 données.
     */
    public function getCroisementAction(string $feedDataIdX, string $feedDataIdY, string $frequence, string $debut, string $fin): JsonResponse
    {
        $feedDataX = $this->canSeeFeedData($feedDataIdX);
        $feedDataY = $this->canSeeFeedData($feedDataIdY);

        $frequence = DataValue::getFrequencyFromMachineName(\strtoupper($frequence));
        $debut = DataValue::adaptToFrequency(new \DateTimeImmutable($debut), $frequence);
        $fin = new \DateTimeImmutable($fin . ' 23:59:59');

        // Get data between $debut & $fin for requested frequency.
        $result = $this->dataValueRepository->getXY($debut, $fin, $feedDataX, $feedDataY, $frequence);

        return new JsonResponse(\json_encode($result), 200);
    }

    private function canSeeFeedData(string $feedDataId): FeedData
    {
        if (!$feedData = $this->feedDataRepository->find($feedDataId)) {
            throw new NotFoundHttpException("Le flux de données cherché n'existe pas !");
        }

        if ($feedData->getFeed()->getFeedType() == Feed::FEED_TYPE_METEO) {
            // Tout le monde peut voir les flux de type météo. Ces données ne sont
            // pas personnelles
            return $feedData;
        }

        if (!$placeList = $feedData->getFeed()->getPlaces()) {
            throw new NotFoundHttpException("Le flux de données cherché n'existe pas !");
        }

        // Ici on boucle par principe mais dans les faits : un flux qui n'est pas de type météo
        // est de type électricité ou gaz et ces types de flux ne peuvent théoriquement être reliés
        // qu'à une seule Place.
        foreach ($placeList as $place) {
            if (!$this->getUser()->canSee($place, $this->userCanSharePlace, $this->placeCanBePublic)) {
                throw new AccessDeniedHttpException("Vous n'êtes pas authorisé à voir les données de cette adresse.");
            }
        }

        return $feedData;
    }
}
