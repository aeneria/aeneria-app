<?php

namespace App\Tests\Functional\Command;

use App\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class EditUserCommandTest extends AppTestCase
{
    public function testCommand()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $entityManager = $this->getEntityManager();
        $passwordHasher = $this->getPasswordHasher();

        $user = $this->createPersistedUser([
            'active' => true,
        ]);
        $entityManager->flush();
        $entityManager->clear();

        $command = $application->find('aeneria:user:edit');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'username' => $user->getUsername(),
            '--username' => $newUsername = 'test' . \rand() . '@example.com',
            '--password' => $newPassword = 'test' . \rand(),
            '--active' => false,
        ]);
        self::assertEquals($commandTester->getStatusCode(), 0);

        $userFromRepo = $this->getUserRepository()->find($user->getId());

        self::assertTrue(!$userFromRepo->isActive());
        self::assertEquals($userFromRepo->getUsername(), $newUsername);
        self::assertTrue($passwordHasher->isPasswordValid($userFromRepo, $newPassword));
    }
}
