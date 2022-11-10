<?php

namespace App\Tests\Unit\Entity;

use App\Tests\AppTestCase;

final class PendingActionTest extends AppTestCase
{
    public function testPendingActionInstance()
    {
        $user = $this->createUser();

        $action = $this->createPendingAction([
            'id' => $actionId = \rand(),
            'token' => $token = 'token' . \rand(),
            'user' => $user,
            'action' => $actionName = 'action' . \rand(),
            'expirationDate' => $date = new \DateTimeImmutable(),
            'param' => ['toto' => 'toto'],
        ]);

        self::assertSame($action->getId(), $actionId);
        self::assertSame($action->getToken(), $token);
        self::assertSame($action->getUser(), $user);
        self::assertSame($action->getAction(), $actionName);
        self::assertSame($action->getExpirationDate(), $date);
        self::assertSame($action->getParam(), ['toto' => 'toto']);
    }
}
