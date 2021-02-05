<?php

namespace App\Services;

use App\Entity\Feed;
use App\Entity\PendingAction;
use App\Entity\Place;
use App\Entity\User;
use App\Repository\FeedRepository;
use App\Repository\PendingActionRepository;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\FeedDataProviderFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Data exporter services.
 */
class PendingActionService
{
    const ACTION_DATA_CONNECT_CALLBACK = 'data_connect_callback';
    const ACTION_IMPORT_DATA = 'import_data';
    const ACTION_FETCH_DATA = 'fetch_data';

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

    public function __construct(
        EntityManagerInterface $entityManager,
        PendingActionRepository $actionRepository,
        PlaceRepository $placeRepository,
        FeedRepository $feedRepository,
        DataImporter $dataImporter,
        FeedDataProviderFactory $feedDataProviderFactory
    ) {
        $this->entityManager = $entityManager;

        $this->actionRepository = $actionRepository;
        $this->placeRepository = $placeRepository;
        $this->feedRepository = $feedRepository;

        $this->dataImporter = $dataImporter;
        $this->feedDataProviderFactory = $feedDataProviderFactory;
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

    public function processExpiredPendingAction(PendingAction $action): void
    {
        switch ($action->getAction()) {
            case self::ACTION_DATA_CONNECT_CALLBACK:
                $this->delete($action);
                break;
            case self::ACTION_IMPORT_DATA:
                $this->processDataImportAction($action);
                break;
            case self::ACTION_FETCH_DATA:
                $this->processDataFetchAction($action);
                break;
        }

        $this->delete($action);
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

    public function processDataImportAction(PendingAction $action): void
    {
        if (self::ACTION_IMPORT_DATA !== $action->getAction()) {
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'est pas du type " . self::ACTION_IMPORT_DATA);
        }
        if (!$placeId = $action->getSingleParam('place')) {
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'place'");
        }
        if (!$place = $this->placeRepository->find($placeId)) {
            $this->delete($action);
            throw new \InvalidArgumentException("Impossible de trouver la place avec l'id " . $placeId);
        }

        if (!$filename = $action->getSingleParam('filename')) {
            $this->delete($action);
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'filename'");
        }
        try {
            $this->dataImporter->importPlace($place, $filename);
        } catch (\Exception $e) {
            echo $e->getMessage();
        } finally {
            \unlink($filename);
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
                'start' => $start,
                'end' => $end,
                'force' => $force,
            ]
        );
    }

    public function processDataFetchAction(PendingAction $action): void
    {
        if (self::ACTION_FETCH_DATA !== $action->getAction()) {
            throw new \InvalidArgumentException("L'action n'est pas du type " . self::ACTION_FETCH_DATA);
        }
        if (!$feedId = $action->getSingleParam('feed')) {
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'feed'");
        }
        if (!$feed = $this->feedRepository->find($feedId)) {
            throw new \InvalidArgumentException("Impossible de trouver le feed avec l'id " . $feedId);
        }

        if (!$start = $action->getSingleParam('start')) {
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'start'");
        }
        if (!$start instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException("Le paramètre 'start' n'est pas un DateTimeImmutable");
        }

        if (!$end = $action->getSingleParam('end')) {
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'end'");
        }
        if (!$end instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException("Le paramètre 'end' n'est pas un DateTimeImmutable");
        }

        if (!$force = $action->getSingleParam('force')) {
            throw new \InvalidArgumentException("L'action n'a pas de paramètre 'force'");
        }

        $this
            ->feedDataProviderFactory
            ->fromFeed($feed)
            ->fetchDataBetween($start, $end, [$feed], $force)
        ;

        $this->delete($action);
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
