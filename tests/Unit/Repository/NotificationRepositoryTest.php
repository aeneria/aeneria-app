<?php

namespace App\Tests\Unit\Entity;

use App\Tests\AppTestCase;

final class NotificationRepositoryTest extends AppTestCase
{
    public function testPersistAndFind()
    {
        $entityManager = $this->getEntityManager();
        $notificationRepository = $this->getNotificationRepository();

        $notification = $this->createPersistedNotification();
        $entityManager->flush();
        $entityManager->clear();

        $notification = $notificationRepository->find($notification->getId());

        self::assertSame($notification->getId(), $notification->getId());
    }

    public function testFindNotificationForUser()
    {
        $entityManager = $this->getEntityManager();
        $notificationRepository = $this->getNotificationRepository();

        $user = $this->createPersistedUser();
        $this->createPersistedNotification(['user' => $user]);
        $this->createPersistedNotification(['user' => $user]);
        $entityManager->flush();
        $entityManager->clear();

        $notifications = $notificationRepository->findNotificationForUser($user);

        self::assertCount(2, $notifications);
    }
}
