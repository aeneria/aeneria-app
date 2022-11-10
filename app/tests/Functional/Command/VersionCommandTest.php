<?php

namespace App\Tests\Functional\Command;

use App\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class VersionCommandTest extends AppTestCase
{
    public function testCommand()
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);

        $command = $application->find('aeneria:version');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertEquals($commandTester->getStatusCode(), 0);
        self::assertEquals($commandTester->getDisplay(), $this->getParameter('aeneria.version'));
    }
}
