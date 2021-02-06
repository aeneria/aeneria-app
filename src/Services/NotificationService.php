<?php

namespace App\Services;

use App\Entity\Notification;
use App\Entity\Place;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Notification services.
 */
class NotificationService
{
    /** @var NotificationRepository */
    private $notificationRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        NotificationRepository $notificationRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->entityManager = $entityManager;
    }

    public function createNotification(
        User $user,
        Place $place = null,
        string $level,
        string $type,
        string $message
    ): Notification {
        $notification = (new Notification())
            ->setUser($user)
            ->setPlace($place)
            ->setType($level)
            ->setType($type)
            ->setDate(new \DateTimeImmutable())
            ->setMessage($message)
        ;

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    public function deleteNotification(Notification $notification): void
    {
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }
}