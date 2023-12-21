<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class AddUserCommandTest extends AppTestCase
{
    public function testCommand()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);
        $passwordHasher = $this->getPasswordHasher();

        $command = $application->find('aeneria:user:add');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'username' => $username = 'test' . \rand() . '@example.com',
            'password' => $password = 'test' . \rand(),
        ]);
        self::assertEquals($commandTester->getStatusCode(), 0);

        $userFromRepo = $this->getUserRepository()->findOneByUsername($username);

        self::assertTrue($passwordHasher->isPasswordValid($userFromRepo, $password));
    }
}
