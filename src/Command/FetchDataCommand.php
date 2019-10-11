<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use App\Entity\Feed;
use Doctrine\ORM\EntityManagerInterface;
use App\Object\Linky;
use App\Entity\DataValue;
use App\Object\MeteoFrance;
use App\Repository\FeedRepository;

/**
 * Defined command to refresh all feeds
 * @todo Simplify, no need for callbacks, just make Linky and MeteoFrance implements a same interface
 *
 */
class FetchDataCommand extends Command
{
    private $entityManager;
    private $feedRepository;

    public function __construct(EntityManagerInterface $entityManager, FeedRepository $feedRepository)
    {
        $this->entityManager = $entityManager;
        $this->feedRepository = $feedRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console").
        ->setName('pilea:fetch-data')

        // the short description shown while running "php bin/console list".
        ->setDescription('Get daily data from all feed')

        // the full command description shown when running the command with
        // the "--help" option.
        ->setHelp('This command allows you to fetch yesterday data for all defined feeds')

        // argument to know if we want to force refresh.
        ->addArgument('force', InputArgument::REQUIRED, 'Refresh data for $date even if it already exists ?')

        // argument to know if we want to force refresh.
        ->addArgument('date', InputArgument::OPTIONAL, 'The date we want to fetch data format Y-m-d, if not given, fetch data for yesterday.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $force = filter_var($input->getArgument('force'), FILTER_VALIDATE_BOOLEAN);

        // We fetch all Feeds data.
        $feeds = $this->feedRepository->findAllActive();

        // If a date is given, we update only for this date.
        if($date=$input->getArgument('date')) {
            $date = new \DateTime($date);
            // For each feeds, we call the right method to fetch data.
            /** @var \App\Entity\Feed $feeds */
            foreach($feeds as $feed) {
                $feed->fetchDataFor($this->entityManager, $date, $force);
            }
        }
        // Else we update from last data to yesterday.
        else {
            // Get yesterday datetime.
            $date = new \DateTime();
            $date->sub(new \DateInterval('P1D'));
            $date = new \DateTime($date->format("Y-m-d 00:00:00"));

            // For each feeds, we call the right method to fetch data.
            /** @var \App\Entity\Feed $feeds */
            foreach($feeds as $feed) {
                $feed->fetchDataUntilLastUpdateTo($this->entityManager, $date);
            }
        }
    }
}
