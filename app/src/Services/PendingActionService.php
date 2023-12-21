<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Feed;
use App\Entity\PendingAction;
use App\Entity\Place;
use App\Entity\User;
use App\Model\FetchingError;
use App\Repository\FeedRepository;
use App\Repository\PendingActionRepository;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\FeedDataProviderFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Data exporter services.
 */
class PendingActionService
{
    public const ACTION_DATA_CONNECT_CALLBACK = 'data_connect_callback';
    public const ACTION_GRDF_ADICT_CALLBACK = 'grdf_adict_callback';
    public const ACTION_IMPORT_DATA = 'import_data';
    public const ACTION_FETCH_DATA = 'fetch_data';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PendingActionRepository */
    private $actionRepository;
    /** @var PlaceRepository */
    private $placeRepository;
    /** @var FeedRepository */
    private $feedRepository;

    /** @var DataImporter */
    private $dataImporter;
    /** @var FeedDataProviderFactory */
    private $feedDataProviderFactory;
    /** @var NotificationService */
    private $notificationService;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        PendingActionRepository $actionRepository,
        PlaceRepository $placeRepository,
        FeedRepository $feedRepository,
        DataImporter $dataImporter,
        FeedDataProviderFactory $feedDataProviderFactory,
        NotificationService $notificationService,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;

        $this->actionRepository = $actionRepository;
        $this->placeRepository = $placeRepository;
        $this->feedRepository = $feedRepository;

        $this->dataImporter = $dataImporter;
        $this->feedDataProviderFactory = $feedDataProviderFactory;
        $this->notificationService = $notificationService;

        $this->logger = $logger;
    }

    public function processAllExpiredPendingActions(): void
    {
        $actions = $this->actionRepository->findExpiredActions();

        if ($actions) {
            foreach ($actions as $action) {
                $this->processExpiredPendingAction($action);
            }
        }
    }

    private function processExpiredPendingAction(PendingAction $action): void
    {
        switch ($action->getAction()) {
            case self::ACTION_DATA_CONNECT_CALLBACK:
                $this->logger->info("Pending Action - Delete unused DataConnectCallback action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
                $this->delete($action);
                break;
            case self::ACTION_IMPORT_DATA:
                $this->processDataImportAction($action);
                break;
            case self::ACTION_FETCH_DATA:
                $this->processDataFetchAction($action);
                break;
        }
    }

    public function createDataConnectCallbackAction(User $user, Place $place = null): PendingAction
    {
        $param = $place ? ['place' => $place->getId()] : [];

        return $this->createAction(
            self::ACTION_DATA_CONNECT_CALLBACK,
            $user,
            new \DateTimeImmutable('now + 1 day'),
            $param
        );
    }

    public function createGrdfAdictCallbackAction(User $user, Place $place = null): PendingAction
    {
        $param = $place ? ['place' => $place->getId()] : [];

        return $this->createAction(
            self::ACTION_GRDF_ADICT_CALLBACK,
            $user,
            new \DateTimeImmutable('now + 1 day'),
            $param
        );
    }

    public function createDataImportAction(User $user, Place $place, string $filename): PendingAction
    {
        return $this->createAction(
            self::ACTION_IMPORT_DATA,
            $user,
            new \DateTimeImmutable('now'),
            [
                'place' => $place->getId(),
                'filename' => $filename,
            ]
        );
    }

    private function processDataImportAction(PendingAction $action): void
    {
        $this->logger->debug("Pending Action - Start processing Import Data Action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);

        if (self::ACTION_IMPORT_DATA !== $action->getAction()) {
            $this->logger->error("Pending Action - Wrong type, delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'est pas du type " . self::ACTION_IMPORT_DATA);
        }
        if (!$placeId = $action->getSingleParam('place')) {
            $this->logger->error("Pending Action - Missing parameter 'place', delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'place'");
        }
        if (!$place = $this->placeRepository->find($placeId)) {
            $this->logger->error("Pending Action - Unfound place, delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId(), 'place' => $placeId]);
            $this->delete($action);
            throw new \InvalidArgumentException("Impossible de trouver la place avec l'id " . $placeId);
        }

        if (!$filename = $action->getSingleParam('filename')) {
            $this->logger->error("Pending Action - Missing parameter 'filename', delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'filename'");
        }
        try {
            $errors = $this->dataImporter->importPlace($place, $filename);

            $this->notificationService->handleImportNotification($action->getUser(), $place, $errors);
        } catch (\Exception $e) {
            $this->logger->error("Pending Action - Error while processing action", ['user' => $action->getUser()->getId(), 'action' => $action->getId(), 'message' => $e->getMessage()]);

            $this->notificationService->handleImportNotification($action->getUser(), $place, [$e->getMessage()]);
        } finally {
            try {
                \unlink($filename);
            } catch (\Exception $e) {
            }

            $this->logger->info("Pending Action - Import data action processed, delete it", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
        }
    }

    public function createDataFetchAction(User $user, Feed $feed, \DateTimeImmutable $start, \DateTimeImmutable $end, bool $force): PendingAction
    {
        return $this->createAction(
            self::ACTION_FETCH_DATA,
            $user,
            new \DateTimeImmutable('now'),
            [
                'feed' => $feed->getId(),
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'force' => $force,
            ]
        );
    }

    private function processDataFetchAction(PendingAction $action): void
    {
        $this->logger->debug("Pending Action - Start processing Fetch Data Action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);

        if (self::ACTION_FETCH_DATA !== $action->getAction()) {
            $this->logger->error("Pending Action - Wrong type, delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'est pas du type " . self::ACTION_FETCH_DATA);
        }
        if (!$feedId = $action->getSingleParam('feed')) {
            $this->logger->error("Pending Action - Missing parameter 'feed', delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'feed'");
        }
        if (!$feed = $this->feedRepository->find($feedId)) {
            $this->logger->error("Pending Action - Feed unfound, delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId(), 'feed' => $feedId]);
            $this->delete($action);
            throw new \InvalidArgumentException("Impossible de trouver le feed avec l'id " . $feedId);
        }

        if (!$start = \DateTimeImmutable::createFromFormat('Y-m-d', $action->getSingleParam('start'))) {
            $this->logger->error("Pending Action - Missing parameter 'start', delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'start' ou celui-ci est mal formé.");
        }

        if (!$end = \DateTimeImmutable::createFromFormat('Y-m-d', $action->getSingleParam('end'))) {
            $this->logger->error("Pending Action - Missing parameter 'end', delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'end' ou celui-ci est mal formé.");
        }

        if (null === ($force = $action->getSingleParam('force'))) {
            $this->logger->error("Pending Action - Missing parameter 'force', delete action", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'force'");
        }

        try {
            $errors = $this
                ->feedDataProviderFactory
                ->fromFeed($feed)
                ->fetchDataBetween($start, $end, [$feed], $force)
            ;

            $this->notificationService->handleFetchDataNotification($action->getUser(), $feed, $errors);
        } catch (\Exception $e) {
            $this->logger->error("Pending Action - Error while processing action", ['user' => $action->getUser()->getId(), 'action' => $action->getId(), 'message' => $e->getMessage()]);

            $this->notificationService->handleFetchDataNotification($action->getUser(), $feed, [new FetchingError($feed, $start, $e)]);
        } finally {
            $this->logger->info("Pending Action - Fetch data action processed, delete it", ['user' => $action->getUser()->getId(), 'action' => $action->getId()]);
            $this->delete($action);
        }
    }

    private function createAction(string $action, User $user, \DateTimeImmutable $expirationDate, array $param): PendingAction
    {
        $token = \bin2hex(\openssl_random_pseudo_bytes(PendingAction::TOKEN_LENGTH / 2));

        $action = (new PendingAction())
            ->setToken($token)
            ->setUser($user)
            ->setAction($action)
            ->setExpirationDate($expirationDate)
            ->setParam($param)
        ;

        $this->entityManager->persist($action);
        $this->entityManager->flush();

        return $action;
    }

    public function findActionByToken(User $user, string $token): PendingAction
    {
        if (!$action = $this->actionRepository->findOneBy(['token' => $token])) {
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
