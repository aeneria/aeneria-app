<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Tests\AppTestCase;

final class PendingActionRepositoryTest extends AppTestCase
{
    public function testPersistAndFind()
    {
        $entityManager = $this->getEntityManager();
        $pendingActionRepository = $this->getPendingActionRepository();

        $action = $this->createPersistedPendingAction();
        $entityManager->flush();
        $entityManager->clear();

        $actionFromRepo = $pendingActionRepository->find($action->getId());

        self::assertSame($action->getId(), $actionFromRepo->getId());
    }

    public function testFindExpiredActions()
    {
        $entityManager = $this->getEntityManager();
        $pendingActionRepository = $this->getPendingActionRepository();

        $action = $this->createPersistedPendingAction(['expirationDate' => new \DateTimeImmutable('yesterday')]);
        $entityManager->flush();
        $entityManager->clear();

        $keys = [];
        foreach ($pendingActionRepository->findExpiredActions() as $pendingAction) {
            $keys[] = $pendingAction->getId();
        }

        self::assertContains($action->getId(), $keys);
    }
}
