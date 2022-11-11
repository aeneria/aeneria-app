<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Feed;
use App\Entity\Place;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class DoctrineEventSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
      $entity = $args->getObject();
      if ($entity instanceof User || $entity instanceof Place || $entity instanceof Feed) {
        $entity->setCreatedAt(new \DateTimeImmutable());
        $entity->setUpdatedAt(new \DateTimeImmutable());
      }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
      $entity = $args->getObject();
      if ($entity instanceof Place || $entity instanceof Feed) {
        $entity->setUpdatedAt(new \DateTimeImmutable());
      }
    }
}