<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class DeactivateUserCommandTest extends AppTestCase
{
    public function testCommand()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $entityManager = $this->getEntityManager();

        $user = $this->createPersistedUser(['active' => true]);
        $entityManager->flush();
        $entityManager->clear();

        $command = $application->find('aeneria:user:deactivate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['username' => $user->getUsername()]);
        self::assertEquals($commandTester->getStatusCode(), 0);

        $userFromRepo = $this->getUserRepository()->find($user->getId());

        self::assertTrue(!$userFromRepo->isActive());
    }
}
