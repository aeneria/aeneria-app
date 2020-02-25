<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * Get current version of aeneria.
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
            ->setName('aeneria:version')
            ->setDescription('Get aeneria version.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write($this->parameters->get('aeneria.version'));

        return 0;
    }
}