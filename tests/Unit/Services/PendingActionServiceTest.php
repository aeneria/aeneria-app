<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Entity\PendingAction;
use App\Services\DataImporter;
use App\Services\FeedDataProvider\FeedDataProviderFactory;
use App\Services\PendingActionService;
use App\Tests\AppTestCase;

final class PendingActionServiceTest extends AppTestCase
{
    private function createPendingActionService(): PendingActionService
    {
        return new PendingActionService(
            $this->getEntityManager(),
            $this->getPendingActionRepository(),
            $this->getPlaceRepository(),
            $this->getFeedRepository(),
            $this->createMock(DataImporter::class),
            $this->createMock(FeedDataProviderFactory::class),
            $this->getLogger()
        );
    }
    public function testCreateDataConnectCallbackAction()
    {
        $actionService = $this->createPendingActionService();

        $user = $this->createPersistedUser();
        $place = $this->createPersistedPlace(['user' => $user]);

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

    public function testFindActionByToken()
    {
        $actionService = $this->createPendingActionService();

        $user = $this->createPersistedUser();
        $place = $this->createPersistedPlace(['user' => $user]);

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        $actionFromRepo = $actionService->findActionByToken($user, $action->getToken());

        self::assertSame($action, $actionFromRepo);
    }

    public function testFindActionByTokenDeniedWrongUser()
    {
        $actionService = $this->createPendingActionService();

        $user = $this->createPersistedUser();
        $user2 = $this->createPersistedUser();
        $place = $this->createPersistedPlace(['user' => $user]);

        $action = $actionService->createDataConnectCallbackAction($user, $place);

        $this->expectExceptionMessage("Le token ne correspond pas à l'utilisateur courant");
        $actionService->findActionByToken($user2, $action->getToken());
    }

    public function testFindActionByTokenUnknownToken()
    {
        $actionService = $this->createPendingActionService();

        $user = $this->createPersistedUser();

        $this->expectExceptionMessage("Impossible de trouver la demande correspondante");
        $actionService->findActionByToken($user, 'totoot');
    }
}
