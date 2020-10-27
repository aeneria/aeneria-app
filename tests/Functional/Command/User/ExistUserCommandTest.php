<?php

namespace App\Tests\Functional\Command;

use App\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class ExistUserCommandTest extends AppTestCase
{
    public function testCommand()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);

        $command = $application->find('aeneria:user:exist');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['username' => 'user-test@example.com']);
        self::assertEquals($commandTester->getStatusCode(), 0);
        self::assertEquals($commandTester->getDisplay(), 1);

        // Si quelqu'un à un jour créer cet utilisateur, eh bien, dommage !
        $commandTester->execute(['username' => 'POUET POUET POUET' . \rand() . '@example.com']);
        self::assertEquals($commandTester->getStatusCode(), 0);
        self::assertEquals($commandTester->getDisplay(), 0);
    }
}
