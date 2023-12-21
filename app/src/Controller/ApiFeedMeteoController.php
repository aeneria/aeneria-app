<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Feed;
use App\Repository\FeedRepository;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\MeteoFranceDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiFeedMeteoController extends AbstractAppController
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var FeedRepository */
    private $feedRepository;
    /** @var MeteoFranceDataProvider */
    private $meteoFranceDataProvider;

    public function __construct(
        bool $userCanSharePlace,
        bool $placeCanBePublic,
        bool $isDemoMode,
        PlaceRepository $placeRepository,
        FeedRepository $feedRepository,
        EntityManagerInterface $entityManager,
        MeteoFranceDataProvider $meteoFranceDataProvider
    ) {
        parent::__construct(
            $userCanSharePlace,
            $placeCanBePublic,
            $isDemoMode,
            $placeRepository
        );

        $this->entityManager = $entityManager;
        $this->feedRepository = $feedRepository;
        $this->feedRepository = $feedRepository;
        $this->meteoFranceDataProvider = $meteoFranceDataProvider;
    }

    public function getMeteoStationList(): JsonResponse
    {
        return new JsonResponse($this->meteoFranceDataProvider->getAvailableStations(), 200);
    }

    public function update(string $placeId, Request $request): JsonResponse
    {
        $place = $this->checkPlace($placeId);
        $data = \json_decode($request->getContent());

        if (!$data->meteo) {
            return $this->dataValidationErrorResponse('meteo', "Vous devez fournir un id de station meteo 'meteo'.");
        }
        if (!$station = $this->meteoFranceDataProvider->findStationByKey((int) $data->meteo)) {
            return $this->dataValidationErrorResponse('meteo', \sprintf("La station météo '%s' n'existe pas.", $data->meteo));
        }
        $meteoFeed = $this->feedRepository->getOrCreateMeteoFranceFeed([
            'STATION_ID' => $station->key,
            'CITY' => $station->label,
        ]);

        if ($oldMeteoFranceFeed = $place->getFeed(Feed::FEED_TYPE_METEO)) {
            $place->removeFeed($oldMeteoFranceFeed);
        }
        $place->addFeed($meteoFeed);

        $this->entityManager->persist($meteoFeed);

        $this->entityManager->persist($place);
        $this->entityManager->flush();

        return new JsonResponse($place->jsonSerialize(), 200);
    }
}
