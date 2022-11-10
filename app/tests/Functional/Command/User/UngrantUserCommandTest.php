<?php

namespace App\Tests\Functional\Command;

use App\Entity\User;
use App\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class UngrantUserCommandTest extends AppTestCase
{
    public function testCommand()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $entityManager = $this->getEntityManager();

        $user = $this->createPersistedUser(['roles' => [User::ROLE_ADMIN]]);
        $entityManager->flush();
        $entityManager->clear();

        $command = $application->find('aeneria:user:ungrant');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['username' => $user->getUsername()]);
        self::assertEquals($commandTester->getStatusCode(), 0);

        $userFromRepo = $this->getUserRepository()->find($user->getId());

        self::assertTrue(!$userFromRepo->isAdmin());
    }
}
