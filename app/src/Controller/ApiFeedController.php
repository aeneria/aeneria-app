<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Services\PendingActionService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiFeedController extends AbstractAppController
{
    public function dataRefresh(
        bool $userCanFetch,
        Request $request,
        PendingActionService $pendingActionService
    ): JsonResponse {
        if (!$userCanFetch) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->getUser();
        \assert($user instanceof User);

        $data = \json_decode($request->getContent());

        if (!$data->placeId) {
            return $this->dataValidationErrorResponse('placeId', "Vous devez fournir un id d'adresse 'placeId'.");
        }

        if (!$data->feedId) {
            return $this->dataValidationErrorResponse('feedIdaceId', "Vous devez fournir un id de flux 'feedId'.");
        }

        if (!$data->start || !$startDate = \DateTimeImmutable::createFromFormat('!d/m/Y', $data->start)) {
            return $this->dataValidationErrorResponse('start', "Vous devez fournir une date de début 'start' au format `dd/mm/yyyy`.");
        }

        if (!$data->end || !$endDate = \DateTimeImmutable::createFromFormat('!d/m/Y', $data->end)) {
            return $this->dataValidationErrorResponse('end', "Vous devez fournir une date de fin 'end' au format `dd/mm/yyyy`.");
        }

        $place = $this->checkPlace($data->placeId);

        if (!$feed = $place->findFeed($data->feedId)) {
            return $this->dataValidationErrorResponse('feedId', "Le flux donné ne correspond pas à l'adresse donnée.");
        }

        $pendingActionService->createDataFetchAction(
            $user,
            $feed,
            $startDate,
            $endDate,
            (bool) ($data->force ?? false)
        );

        return new JsonResponse('', 200);
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
        if (!$request->request->has('feedId')) {
            return $this->dataValidationErrorResponse('feedId', "Vous devez fournir un id de flux 'feedId'.");
        }
        if (!$request->files->has('file')) {
            return $this->dataValidationErrorResponse('file', "Vous devez fournir un fichier à importer 'file'.");
        }

        $place = $this->checkPlace($request->request->get('placeId'));

        if (!$feed = $place->findFeed((int)$request->request->get('feedId'))) {
            return $this->dataValidationErrorResponse('feedId', "Le flux donné ne correspond pas à l'adresse donnée.");
        }

        $file = $request->files->get('file');
        \assert($file instanceof File);
        $file->move(
            $directory = \sprintf('%s/private/dataImport', $projectDir),
            $filename = \uniqid()
        );

        $pendingActionService->createDataImportAction(
            $user,
            $place,
            $feed,
            \sprintf('%s/%s', $directory, $filename)
        );

        return new JsonResponse('', 200);
    }
}
