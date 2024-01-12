<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Place;
use App\Entity\User;
use App\Repository\FeedRepository;
use App\Repository\UserRepository;
use App\Services\DataExporter;
use App\Services\FeedDataProvider\MeteoFranceDataProvider;
use App\Services\PendingActionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiPlaceController extends AbstractAppController
{
    public function create(
        int $userMaxPlaces,
        Request $request,
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        MeteoFranceDataProvider $meteoFranceDataProvider
    ): JsonResponse {
        $user = $this->getUser();
        \assert($user instanceof User);

        if ((-1 != $userMaxPlaces && \count($user->getPlaces()) >= $userMaxPlaces) || $this->isDemoMode) {
            throw new AccessDeniedHttpException("Vous ne pouvez plus créer d'adresse.");
        }

        $data = \json_decode($request->getContent());

        if (!$data->name) {
            return $this->dataValidationErrorResponse('name', "Vous devez fournir un nom 'name'.");
        }
        if (!$data->meteo) {
            return $this->dataValidationErrorResponse('meteo', "Vous devez fournir un id de station meteo 'meteo'.");
        }
        if (!$station = $meteoFranceDataProvider->findStationByKey((int) $data->meteo)) {
            return $this->dataValidationErrorResponse('meteo', \sprintf("La station météo '%s' n'existe pas.", $data->meteo));
        }

        $place = new Place();
        $place->setUser($user);
        $place->setName((string) $data->name);

        $meteoFeed = $feedRepository->getOrCreateMeteoFranceFeed([
            'STATION_ID' => $station->key,
            'CITY' => $station->label,
        ]);

        $place->addFeed($meteoFeed);
        $entityManager->persist($meteoFeed);

        $entityManager->persist($place);
        $entityManager->flush();

        return new JsonResponse($place->jsonSerialize(), 200);
    }

    public function update(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): JsonResponse {
        $data = \json_decode($request->getContent());

        if (!$data->placeId) {
            return $this->dataValidationErrorResponse('placeId', "Vous devez fournir un id d'adresse 'placeId'.");
        }
        if (!$data->name) {
            return $this->dataValidationErrorResponse('name', "Vous devez fournir un nouveau nom 'name'.");
        }
        if ($this->userCanSharePlace && !(null === $data->allowedUsers || \is_array($data->allowedUsers))) {
            return $this->dataValidationErrorResponse('sharedWith', "Vous devez fournir un attribut 'sharedWith' de type array.");
        }
        if ($this->placeCanBePublic && !\is_bool($data->public) && null === $data->public) {
            return $this->dataValidationErrorResponse('isPublic', "Vous devez fournir un attribut 'isPublic' de type booleen.");
        }

        $place = $this->checkPlace($data->placeId);
        $place->setName($data->name);

        if ($this->userCanSharePlace && \count($data->allowedUsers)) {
            $users = $userRepository->findBy(['id' => \array_map(
                function ($user) {
                    return $user->id;
                },
                $data->allowedUsers
            )]);
            $place->setAllowedUsers($users);
        } else {
            $place->setAllowedUsers([]);
        }

        if ($this->placeCanBePublic) {
            $place->setPublic($data->public);
        } else {
            $place->setPublic(false);
        }

        $entityManager->persist($place);
        $entityManager->flush();

        return new JsonResponse('', 200);
    }

    public function delete(Request $request): JsonResponse
    {
        $data = \json_decode($request->getContent());

        if (!$data->placeId) {
            return $this->dataValidationErrorResponse('placeId', "Vous devez fournir un id d'adresse 'placeId'.");
        }

        $place = $this->checkPlace($data->placeId);
        $place = $this->placeRepository->purge($place);

        return new JsonResponse('', 200);
    }

    public function dataExport(
        bool $userCanExport,
        Request $request,
        DataExporter $dataExporter
    ): Response {
        if (!$userCanExport) {
            throw new AccessDeniedHttpException();
        }

        $data = $request->request->all();

        if (!\array_key_exists('placeId', $data)) {
            return $this->dataValidationErrorResponse('placeId', "Vous devez fournir un id d'adresse 'placeId'.");
        }

        $startDate = \array_key_exists('start', $data) && $data['start'] ? \DateTimeImmutable::createFromFormat('!d/m/Y', $data['start']) : null;
        $endDate = \array_key_exists('end', $data) && $data['end'] ? \DateTimeImmutable::createFromFormat('!d/m/Y', $data['end']) : null;

        $place = $this->checkPlace($data['placeId']);

        $filename = $dataExporter->exportPlace($place, $startDate, $endDate);
        $file = new File($filename);

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());

        return $response;
    }

    public function dataImport(
        bool $userCanImport,
        Request $request,
        PendingActionService $pendingActionService,
        string $projectDir
    ): JsonResponse {
        if (!$userCanImport) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->getUser();
        \assert($user instanceof User);

        if (!$request->request->has('placeId')) {
            return $this->dataValidationErrorResponse('placeId', "Vous devez fournir un id d'adresse 'placeId'.");
        }
        if (!$request->files->has('file')) {
            return $this->dataValidationErrorResponse('file', "Vous devez fournir un fichier à importer 'file'.");
        }

        $place = $this->checkPlace($request->request->get('placeId'));

        $file = $request->files->get('file');
        \assert($file instanceof File);
        $file->move(
            $directory = \sprintf('%s/private/dataImport', $projectDir),
            $filename = \uniqid()
        );

        $pendingActionService->createDataImportAction(
            $user,
            $place,
            null,
            \sprintf('%s/%s', $directory, $filename)
        );

        return new JsonResponse('', 200);
    }
}
