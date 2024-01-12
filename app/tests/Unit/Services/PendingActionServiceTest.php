<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Entity\PendingAction;
use App\Repository\FeedRepository;
use App\Repository\PendingActionRepository;
use App\Repository\PlaceRepository;
use App\Services\DataImporter;
use App\Services\FeedDataProvider\FeedDataProviderFactory;
use App\Services\FeedDataProvider\FeedDataProviderInterface;
use App\Services\NotificationService;
use App\Services\PendingActionService;
use App\Tests\AppTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class PendingActionServiceTest extends AppTestCase
{
    public function testCreateDataConnectCallbackAction()
    {
        $user = $this->createUser(['id' => 0]);
        $place = $this->createPlace(['user' => $user]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('persist')
        ;
        $entityManager
            ->expects($this->exactly(2))
            ->method('flush')
        ;

        $actionService = new PendingActionService(
            $entityManager,
            $this->createMock(PendingActionRepository::class),
            $this->createMock(PlaceRepository::class),
            $this->createMock(FeedRepository::class),
            $this->createMock(DataImporter::class),
            $this->createMock(FeedDataProviderFactory::class),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        self::assertSame($action->getUser(), $user);
        self::assertTrue($action->existParam('place'));
        self::assertSame($action->getSingleParam('place'), $place->getId());
        self::assertSame($action->getAction(), PendingActionService::ACTION_DATA_CONNECT_CALLBACK);
        self::assertEquals(PendingAction::TOKEN_LENGTH, \strlen($action->getToken()));
        self::assertLessThan(new \DateTimeImmutable('now + 1 day'), $action->getExpirationDate());

        $action2 = $actionService->createDataConnectCallbackAction($user);

        self::assertFalse($action2->existParam('place'));
        self::assertNotEquals($action->getToken(), $action2->getToken());
    }

    public function testCreateDataFetchAction()
    {
        $user = $this->createUser(['id' => 0]);
        $feed = $this->createFeed();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(1))
            ->method('persist')
        ;
        $entityManager
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $actionService = new PendingActionService(
            $entityManager,
            $this->createMock(PendingActionRepository::class),
            $this->createMock(PlaceRepository::class),
            $this->createMock(FeedRepository::class),
            $this->createMock(DataImporter::class),
            $this->createMock(FeedDataProviderFactory::class),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );

        $action = $actionService->createDataFetchAction(
            $user,
            $feed,
            $startDate = new \DateTimeImmutable('yesterday'),
            $endDate = new \DateTimeImmutable('last month'),
            false
        );

        self::assertSame($action->getUser(), $user);
        self::assertTrue($action->existParam('feed'));
        self::assertSame($action->getSingleParam('feed'), $feed->getId());
        self::assertTrue($action->existParam('start'));
        self::assertEquals($action->getSingleParam('start'), $startDate->format('Y-m-d'));
        self::assertTrue($action->existParam('end'));
        self::assertEquals($action->getSingleParam('end'), $endDate->format('Y-m-d'));
        self::assertTrue($action->existParam('force'));
        self::assertSame($action->getSingleParam('force'), false);
        self::assertSame($action->getAction(), PendingActionService::ACTION_FETCH_DATA);
        self::assertLessThan(new \DateTimeImmutable('now + 1 day'), $action->getExpirationDate());
    }

    public function testCreateDataImportAction()
    {
        $user = $this->createUser(['id' => 0]);
        $place = $this->createPlace(['user' => $user]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(1))
            ->method('persist')
        ;
        $entityManager
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $actionService = new PendingActionService(
            $entityManager,
            $this->createMock(PendingActionRepository::class),
            $this->createMock(PlaceRepository::class),
            $this->createMock(FeedRepository::class),
            $this->createMock(DataImporter::class),
            $this->createMock(FeedDataProviderFactory::class),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );

        $action = $actionService->createDataImportAction($user, $place, null, 'totototo');

        self::assertSame($action->getUser(), $user);
        self::assertTrue($action->existParam('place'));
        self::assertSame($action->getSingleParam('place'), $place->getId());
        self::assertTrue($action->existParam('filename'));
        self::assertSame($action->getSingleParam('filename'), 'totototo');
        self::assertSame($action->getAction(), PendingActionService::ACTION_IMPORT_DATA);
        self::assertLessThan(new \DateTimeImmutable('now + 1 day'), $action->getExpirationDate());
    }

    public function testFindActionByToken()
    {
        $user = $this->createUser(['id' => 0]);
        $place = $this->createPlace(['user' => $user]);

        $repository = $this->createMock(PendingActionRepository::class);

        $actionService = new PendingActionService(
            $this->createMock(EntityManagerInterface::class),
            $repository,
            $this->createMock(PlaceRepository::class),
            $this->createMock(FeedRepository::class),
            $this->createMock(DataImporter::class),
            $this->createMock(FeedDataProviderFactory::class),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        $repository
            ->expects($this->exactly(1))
            ->method('findOneBy')
            ->willReturn($action)
        ;

        $actionFromRepo = $actionService->findActionByToken($user, $action->getToken());

        self::assertSame($action, $actionFromRepo);
    }

    public function testFindActionByTokenDeniedWrongUser()
    {
        $user = $this->createUser(['id' => 0]);
        $user2 = $this->createUser(['id' => 0]);
        $place = $this->createPlace(['user' => $user]);

        $repository = $this->createMock(PendingActionRepository::class);

        $actionService = new PendingActionService(
            $this->createMock(EntityManagerInterface::class),
            $repository,
            $this->createMock(PlaceRepository::class),
            $this->createMock(FeedRepository::class),
            $this->createMock(DataImporter::class),
            $this->createMock(FeedDataProviderFactory::class),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        $repository
            ->expects($this->exactly(1))
            ->method('findOneBy')
            ->willReturn($action)
        ;

        $this->expectExceptionMessage("Le token ne correspond pas à l'utilisateur courant");
        $actionService->findActionByToken($user2, $action->getToken());
    }

    public function testFindActionByTokenUnknownToken()
    {
        $repository = $this->createMock(PendingActionRepository::class);
        $repository
            ->expects($this->exactly(1))
            ->method('findOneBy')
            ->willReturn(null)
        ;

        $actionService = new PendingActionService(
            $this->createMock(EntityManagerInterface::class),
            $repository,
            $this->createMock(PlaceRepository::class),
            $this->createMock(FeedRepository::class),
            $this->createMock(DataImporter::class),
            $this->createMock(FeedDataProviderFactory::class),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );

        $user = $this->createPersistedUser();

        $this->expectExceptionMessage("Impossible de trouver la demande correspondante");
        $actionService->findActionByToken($user, 'totoot');
    }

    public function testProcessDataConnectCallback()
    {
        $user = $this->createUser(['id' => 1]);
        $action = new PendingAction();
        $action->setId(12);
        $action->setAction(PendingActionService::ACTION_DATA_CONNECT_CALLBACK);
        $action->setUser($user);

        $repository = $this->createMock(PendingActionRepository::class);
        $repository
            ->expects($this->exactly(1))
            ->method('findExpiredActions')
            ->willReturn([$action])
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(1))
            ->method('remove')
        ;
        $entityManager
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $actionService = new PendingActionService(
            $entityManager,
            $repository,
            $this->createMock(PlaceRepository::class),
            $this->createMock(FeedRepository::class),
            $this->createMock(DataImporter::class),
            $this->createMock(FeedDataProviderFactory::class),
            $this->createMock(NotificationService::class),
            $this->getLogger()
        );

        $actionService->processAllExpiredPendingActions();
    }

    public function testProcessImportData()
    {
        $user = $this->createUser();
        $action = new PendingAction();
        $action->setId(12);
        $action->setAction(PendingActionService::ACTION_IMPORT_DATA);
        $action->setUser($user);
        $action->setSingleParam('place', 'userid');
        $action->setSingleParam('filename', 'toto');
        $place = $this->createPlace();

        $repository = $this->createMock(PendingActionRepository::class);
        $repository
            ->expects($this->exactly(1))
            ->method('findExpiredActions')
            ->willReturn([$action])
        ;

        $placeRepository = $this->createMock(PlaceRepository::class);
        $placeRepository
            ->expects($this->exactly(1))
            ->method('find')
            ->willReturn($place)
        ;

        $dataImporter = $this->createMock(DataImporter::class);
        $dataImporter
            ->expects($this->exactly(1))
            ->method('importFile')
            ->willReturn([])
        ;

        $notificationService = $this->createMock(NotificationService::class);
        $notificationService
            ->expects($this->exactly(1))
            ->method('handleImportNotification')
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(1))
            ->method('remove')
        ;
        $entityManager
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $actionService = new PendingActionService(
            $entityManager,
            $repository,
            $placeRepository,
            $this->createMock(FeedRepository::class),
            $dataImporter,
            $this->createMock(FeedDataProviderFactory::class),
            $notificationService,
            $this->getLogger()
        );

        $actionService->processAllExpiredPendingActions();
    }

    public function testProcessFetchData()
    {
        $user = $this->createUser();
        $action = new PendingAction();
        $action->setId(12);
        $action->setAction(PendingActionService::ACTION_FETCH_DATA);
        $action->setUser($user);
        $action->setSingleParam('feed', 'feedID');
        $action->setSingleParam('start', '2021-02-13');
        $action->setSingleParam('end', '2021-03-04');
        $action->setSingleParam('force', true);
        $feed = $this->createFeed();

        $repository = $this->createMock(PendingActionRepository::class);
        $repository
            ->expects($this->exactly(1))
            ->method('findExpiredActions')
            ->willReturn([$action])
        ;

        $feedRepository = $this->createMock(FeedRepository::class);
        $feedRepository
            ->expects($this->exactly(1))
            ->method('find')
            ->willReturn($feed)
        ;

        $feedDataProvider = $this->createMock(FeedDataProviderInterface::class);
        $feedDataProvider
            ->expects($this->exactly(1))
            ->method('fetchDataBetween')
            ->with(
                \DateTimeImmutable::createFromFormat('Y-m-d', '2021-2-13'),
                \DateTimeImmutable::createFromFormat('Y-m-d', '2021-3-04'),
                [$feed],
                true
            )
            ->willReturn([])
        ;

        $feedDataProviderFactory = $this->createMock(FeedDataProviderFactory::class);
        $feedDataProviderFactory
            ->expects($this->exactly(1))
            ->method('fromFeed')
            ->willReturn($feedDataProvider)
        ;

        $notificationService = $this->createMock(NotificationService::class);
        $notificationService
            ->expects($this->exactly(1))
            ->method('handleFetchDataNotification')
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(1))
            ->method('remove')
        ;
        $entityManager
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $actionService = new PendingActionService(
            $entityManager,
            $repository,
            $this->createMock(PlaceRepository::class),
            $feedRepository,
            $this->createMock(DataImporter::class),
            $feedDataProviderFactory,
            $notificationService,
            $this->getLogger()
        );

        $actionService->processAllExpiredPendingActions();
    }
}
