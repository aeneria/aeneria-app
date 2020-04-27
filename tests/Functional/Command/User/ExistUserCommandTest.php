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
        $commandTester->execute(['username' => 'user-test']);
        $this->assertEquals($commandTester->getStatusCode(), 0);
        $this->assertEquals($commandTester->getDisplay(), 1);

        // Si quelqu'un à un jour créer cet utilisateur, eh bien, dommage !
        $commandTester->execute(['username' => 'POUET POUET POUET' . \rand()]);
        $this->assertEquals($commandTester->getStatusCode(), 0);
        $this->assertEquals($commandTester->getDisplay(), 0);
    }
}
