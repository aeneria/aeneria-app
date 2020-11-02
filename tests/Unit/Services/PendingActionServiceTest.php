<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Entity\PendingAction;
use App\Services\PendingActionService;
use App\Tests\AppTestCase;

final class PendingActionServiceTest extends AppTestCase
{
    public function testCreateDataConnectCallbackAction()
    {
        $actionService = new PendingActionService(
            $this->getEntityManager(),
            $this->getPendingActionRepository()
        );

        $user = $this->createPersistedUser();
        $place = $this->createPersistedPlace(['user' => $user]);

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        self::assertSame($action->getUser(), $user);
        self::assertTrue($action->existParam('place'));
        self::assertSame($action->getSingleParam('place'), $place->getId());
        self::assertSame($action->getAction(), PendingAction::ACTION_DATA_CONNECT_CALLBACK);
        self::assertEquals(PendingAction::TOKEN_LENGTH, \strlen($action->getToken()));
        self::assertLessThan(new \DateTimeImmutable('now + 1 day'), $action->getExpirationDate());

        $action2 = $actionService->createDataConnectCallbackAction($user);
        self::assertFalse($action2->existParam('place'));
        self::assertNotEquals($action->getToken(), $action2->getToken());
    }

    public function testFindDataConnectCallbackAction()
    {
        $actionService = new PendingActionService(
            $this->getEntityManager(),
            $this->getPendingActionRepository()
        );

        $user = $this->createPersistedUser();
        $place = $this->createPersistedPlace(['user' => $user]);

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        $actionFromRepo = $actionService->findDataConnectCallbackAction($user, $action->getToken());

        self::assertSame($action, $actionFromRepo);
    }

    public function testFindDataConnectCallbackActionDeniedWrongUser()
    {
        $actionService = new PendingActionService(
            $this->getEntityManager(),
            $this->getPendingActionRepository()
        );

        $user = $this->createPersistedUser();
        $user2 = $this->createPersistedUser();
        $place = $this->createPersistedPlace(['user' => $user]);

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        $this->expectExceptionMessage("Le token ne correspond pas à l'utilisateur courant");
        $actionService->findDataConnectCallbackAction($user2, $action->getToken());
    }

    public function testFindDataConnectCallbackActionUnknownToken()
    {
        $actionService = new PendingActionService(
            $this->getEntityManager(),
            $this->getPendingActionRepository()
        );

        $user = $this->createPersistedUser();

        $this->expectExceptionMessage("Impossible de trouver la demande correspondante");
        $actionService->findDataConnectCallbackAction($user, 'totoot');
    }
}
