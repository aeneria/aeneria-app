<?php

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
        $passwordEncoder = $this->getPassordEncoder();

        $command = $application->find('aeneria:user:add');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'username' => $username = 'test' . \rand() . '@example.com',
            'password' => $password = 'test' . \rand(),
        ]);
        self::assertEquals($commandTester->getStatusCode(), 0);

        $userFromRepo = $this->getUserRepository()->findOneByUsername($username);

        self::assertTrue($passwordEncoder->isPasswordValid($userFromRepo, $password));
    }
}
