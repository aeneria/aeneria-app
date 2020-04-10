<?php

namespace App\Tests\Fonctionnal\Command;

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
        $passwordEncoder = $this->getPassordEncoder();

        $user = $this->createPersistedUser([
            'active' => true,
        ]);
        $entityManager->flush();
        $entityManager->clear();

        $command = $application->find('aeneria:user:edit');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'username' => $user->getUsername(),
            '--username' => $newUsername = 'test' . \rand(),
            '--password' => $newPassword = 'test' . \rand(),
            '--active' => false,
        ]);
        $this->assertEquals($commandTester->getStatusCode(), 0);

        $userFromRepo = $this->getUserRepository()->find($user->getId());

        $this->assertTrue(!$userFromRepo->isActive());
        $this->assertEquals($userFromRepo->getUsername(), $newUsername);
        $this->assertTrue($passwordEncoder->isPasswordValid($userFromRepo, $newPassword));
    }
}
