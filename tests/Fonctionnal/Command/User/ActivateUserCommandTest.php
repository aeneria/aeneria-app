<?php

namespace App\Tests\Fonctionnal\Command;

use App\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class ActivateUserCommandTest extends AppTestCase
{
    public function testCommand()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $entityManager = $this->getEntityManager();

        $user = $this->createPersistedUser(['active' => false]);
        $entityManager->flush();
        $entityManager->clear();

        $command = $application->find('aeneria:user:activate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['username' => $user->getUsername()]);
        $this->assertEquals($commandTester->getStatusCode(), 0);

        $userFromRepo = $this->getUserRepository()->find($user->getId());

        $this->assertTrue($userFromRepo->isActive());
    }
}
