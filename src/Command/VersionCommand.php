<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * Get current version of pilea.
 */
class VersionCommand extends Command
{
    /** @var ContainerBagInterface */
    private $parameters;

    /**
     * Default constructor
     */
    public function __construct(ContainerBagInterface $parameters)
    {
        $this->parameters = $parameters;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pilea:version')
            ->setDescription('Get Pilea version.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write($this->parameters->get('pilea.version'));

        return 0;
    }
}