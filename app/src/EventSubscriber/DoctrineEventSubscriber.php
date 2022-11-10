<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DoctrineEventSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
      $entity = $args->getObject();

      if ($entity instanceof User) {
        if (!$entity->getCreatedAt()) {
          $entity->setCreatedAt(new \DateTimeImmutable());
        }
        $entity->setUpdatedAt(new \DateTimeImmutable());
      }
    }
}