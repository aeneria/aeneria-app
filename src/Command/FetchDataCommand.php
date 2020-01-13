<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use App\Repository\FeedRepository;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\GenericFeedDataProvider;
use Symfony\Component\Console\Input\InputOption;

/**
 * Get newly data from all feeds
 */
class FetchDataCommand extends Command
{
    private $placeRepository;
    private $feedRepository;
    private $feedDataProvider;

    public function __construct(PlaceRepository $placeRepository, FeedRepository $feedRepository, GenericFeedDataProvider $feedDataProvider)
    {
        $this->placeRepository = $placeRepository;
        $this->feedRepository = $feedRepository;
        $this->feedDataProvider = $feedDataProvider;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pilea:fetch-data')
            ->setDescription('Get newly data from all feeds')
            ->setHelp('This command allows you to fetch newly data for all active feeds')
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, 'A date (Y-m-d), if you want to refresh data for a specific date.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, "Use this option if you want to refresh data even if it already exists (that's relevant only if you precise a date with the --date option)")
            ->addOption('placeid', 'pid', InputOption::VALUE_OPTIONAL, 'A Place ID, if you want to refresh data for a specific place.')
            ->addOption('feedid', 'fid', InputOption::VALUE_OPTIONAL, 'A Feed ID, if you want to refresh data for a specific feed.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('placeid') && $input->getOption('feedid')) {
            throw new \Exception("You can't specify a Place id AND a Feed id, you have to choose !");
        }

        if ($placeId = $input->getOption('placeid')) {
            if (!$place = $this->placeRepository->find($placeId)) {
                throw new \Exception("Can't find a place for id : " . $placeId);
            }

            foreach ($place->getFeeds() as $feed) {
                $this->fetchFor($input, [$feed]);
            }
        } elseif ($feedId = $input->getOption('feedid')) {
            if (!$feed = $this->feedRepository->find($feedId)) {
                throw new \Exception("Can't find a feed for id : " . $feedId);
            }
            $this->fetchFor($input, [$feed]);
        } else {
            $feedDataProviderTypes = ['LINKY', 'METEO_FRANCE'];

            foreach ($feedDataProviderTypes as $feedDataProviderType) {
                // We fetch all Feeds data.
                if ($feeds = $this->feedRepository->findAllActive($feedDataProviderType))
                {
                    $this->fetchFor($input, $feeds);
                }
            }
        }

        return 0;
    }

    private function fetchFor(InputInterface $input, array $feeds)
    {
        if($date = $input->getOption('date')) {
            // If a date is given, we update only for this date.

            $date = new \DateTime($date);
            $this->feedDataProvider->fetchDataFor($date, $feeds, $input->getOption('force'));
        } else {
            // Else we update from last data to yesterday.
            // Get yesterday datetime.
            $date = new \DateTime();
            $date->sub(new \DateInterval('P1D'));
            $date = new \DateTime($date->format("Y-m-d 00:00:00"));
            $this->feedDataProvider->fetchDataUntilLastUpdateTo($date, $feeds);
        }
    }
}
