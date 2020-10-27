<?php

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
}
