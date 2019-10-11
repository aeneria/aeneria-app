<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use App\Constants;

/***
 * Get current version of pilea.
 */
class VersionCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('pilea:version')
            ->setDescription('Get Pilea version.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write(Constants::VERSION);
    }
}