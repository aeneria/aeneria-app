<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class FetchDataCommandTest extends AppTestCase
{
    public function testCommand()
    {
        // @todo tester un cas d'usage plus spécifique, là ça prend tout
        // dont des données de tests cassés :/
        $this->markTestSkipped("À revoir !");

        $kernel = $this->getKernel();
        $application = new Application($kernel);

        $command = $application->find('aeneria:fetch-data');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertEquals($commandTester->getStatusCode(), 0);
    }
}
