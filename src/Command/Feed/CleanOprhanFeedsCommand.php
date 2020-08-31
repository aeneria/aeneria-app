<?php

namespace App\Command\Feed;

use App\Repository\FeedRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Clean All orphan Feeds
 */
class CleanOprhanFeedsCommand extends Command
{
    private $feedRepository;

    public function __construct(FeedRepository $feedRepository)
    {
        $this->feedRepository = $feedRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:feed:clean-orphans')
            ->setDescription('Clean All orphan Feeds')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        $feeds = $this->feedRepository->findOrphans();

        if (!$total = \count($feeds)) {
            $output->writeln(\sprintf("<comment>No orphan feed found.</comment>"));

            return 0;
        }

        if (!$this->io->confirm(\sprintf('<question>%d feeds found, are you sure you want to purge them all ?</question>', $total), false)) {
            $output->writeln(\sprintf("<comment>Action canceled.</comment>"));

            return 0;
        }

        $progress = new ProgressBar($output);
        $progress->setFormat('debug');
        $progress->setMaxSteps($total);

        foreach ($feeds as $feed) {
            $this->feedRepository->purge($feed);
            $progress->advance(1);
        }

        $progress->finish();
        $output->writeln("");

        $this->io->success(\sprintf("%d orphan feeds has been successfully purged.", $total));

        return 0;
    }
}
