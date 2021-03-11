<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Entity\Notification;
use App\Model\FetchingError;
use App\Repository\NotificationRepository;
use App\Services\NotificationService;
use App\Tests\AppTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationServiceTest extends AppTestCase
{
    public function testHandleImportNotificationWithNoError()
    {
        $user = $this->createUser();
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

        $notificationService = new NotificationService(
            $entityManager,
            $this->createMock(NotificationRepository::class),
            $this->getLogger()
        );

        $notification = $notificationService->handleImportNotification(
            $user,
            $place,
            null,
        );

        self::assertSame($notification->getUser(), $user);
        self::assertSame($notification->getPlace(), $place);
        self::assertSame($notification->getLevel(), Notification::LEVEL_SUCCESS);
        self::assertSame($notification->getType(), Notification::TYPE_DATA_IMPORT);
        self::assertContains('réalisé avec succès', $notification->getMessage());
    }

    public function testHandleImportNotificationWithError()
    {
        $user = $this->createUser();
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

        $notificationService = new NotificationService(
            $entityManager,
            $this->createMock(NotificationRepository::class),
            $this->getLogger()
        );

        $notification = $notificationService->handleImportNotification(
            $user,
            $place,
            ['blabla'],
        );

        self::assertSame($notification->getUser(), $user);
        self::assertSame($notification->getPlace(), $place);
        self::assertSame($notification->getLevel(), Notification::LEVEL_ERROR);
        self::assertSame($notification->getType(), Notification::TYPE_DATA_IMPORT);
        self::assertContains('des erreurs', $notification->getMessage());
        self::assertContains('blabla', $notification->getMessage());
    }

    public function testHandleFetchDataNotificationWithNoError()
    {
        $user = $this->createUser();
        $place = $this->createPlace(['user' => $user]);
        $feed = $this->createFeed(['places' => [$place]]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(1))
            ->method('persist')
        ;
        $entityManager
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $notificationService = new NotificationService(
            $entityManager,
            $this->createMock(NotificationRepository::class),
            $this->getLogger()
        );

        $notification = $notificationService->handleFetchDataNotification(
            $user,
            $feed,
            null,
        );

        self::assertSame($notification->getUser(), $user);
        self::assertSame($notification->getPlace(), $place);
        self::assertSame($notification->getLevel(), Notification::LEVEL_SUCCESS);
        self::assertSame($notification->getType(), Notification::TYPE_DATA_FETCH);
        self::assertContains('réalisé avec succès', $notification->getMessage());
    }

    public function testHandleDataFetchNotificationWithError()
    {
        $user = $this->createUser();
        $place = $this->createPlace(['user' => $user]);
        $feed = $this->createFeed(['places' => [$place]]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(1))
            ->method('persist')
        ;
        $entityManager
            ->expects($this->exactly(1))
            ->method('flush')
        ;

        $notificationService = new NotificationService(
            $entityManager,
            $this->createMock(NotificationRepository::class),
            $this->getLogger()
        );

        $error = new FetchingError($feed, new \DateTimeImmutable(), new \Exception('blalba'));

        $notification = $notificationService->handleFetchDataNotification(
            $user,
            $feed,
            [$error],
        );

        self::assertSame($notification->getUser(), $user);
        self::assertSame($notification->getPlace(), $place);
        self::assertSame($notification->getLevel(), Notification::LEVEL_ERROR);
        self::assertSame($notification->getType(), Notification::TYPE_DATA_FETCH);
        self::assertContains('pas été correctement', $notification->getMessage());
        self::assertContains('blalba', $notification->getMessage());
    }

    public function testGetAndDeleteNotificationFor()
    {
        $user = $this->createUser();

        $notifications = [
            new Notification(),
            new Notification(),
            new Notification(),
        ];

        $repository = $this->createMock(NotificationRepository::class);
        $repository
            ->expects($this->exactly(1))
            ->method('findNotificationForUser')
            ->willReturn($notifications)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(1))
            ->method('flush')
        ;
        $entityManager
            ->expects($this->exactly(3))
            ->method('remove')
        ;

        $notificationService = new NotificationService(
            $entityManager,
            $repository,
            $this->getLogger()
        );

        $notificationService->getAndDeleteNotificationFor($user);
    }
}
