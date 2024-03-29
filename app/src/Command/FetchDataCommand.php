<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\FeedRepository;
use App\Repository\PlaceRepository;
use App\Services\FeedDataProvider\FeedDataProviderFactory;
use App\Services\FeedDataProvider\FeedDataProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get newly data from all feeds
 */
class FetchDataCommand extends Command
{
    /** @var PlaceRepository */
    private $placeRepository;
    /** @var FeedRepository */
    private $feedRepository;
    /** @var FeedDataProviderFactory */
    private $feedDataProviderFactory;

    public function __construct(
        PlaceRepository $placeRepository,
        FeedRepository $feedRepository,
        FeedDataProviderFactory $feedDataProviderFactory
    ) {
        $this->placeRepository = $placeRepository;
        $this->feedRepository = $feedRepository;
        $this->feedDataProviderFactory = $feedDataProviderFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:fetch-data')
            ->setDescription('Get newly data from all feeds')
            ->setHelp('This command allows you to fetch newly data for all active feeds')
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, 'A date (Y-m-d), if you want to refresh data for a specific date.')
            ->addOption('startDate', null, InputOption::VALUE_OPTIONAL, 'A date (Y-m-d), if you want to refresh data for a date range.')
            ->addOption('endDate', null, InputOption::VALUE_OPTIONAL, 'A date (Y-m-d), if you want to refresh data for a date range.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, "Use this option if you want to refresh data even if it already exists (that's relevant only if you precise a date with the --date option)")
            ->addOption('placeid', null, InputOption::VALUE_OPTIONAL, 'A Place ID, if you want to refresh data for a specific place.')
            ->addOption('feedid', null, InputOption::VALUE_OPTIONAL, 'A Feed ID, if you want to refresh data for a specific feed.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('date') && ($input->getOption('startDate') || $input->getOption('endDate'))) {
            throw new \Exception("You can't specify a date AND a range of dates, you have to choose !");
        }

        if (
            ($input->getOption('startDate') || $input->getOption('endDate')) &&
            !($input->getOption('startDate') && $input->getOption('endDate'))) {
            throw new \Exception("If you specify a start date you have to specify an end date ! (or contrary)");
        }

        if ($input->getOption('placeid') && $input->getOption('feedid')) {
            throw new \Exception("You can't specify a Place id AND a Feed id, you have to choose !");
        }

        if ($placeId = $input->getOption('placeid')) {
            if (!$place = $this->placeRepository->find($placeId)) {
                throw new \Exception("Can't find a place for id : " . $placeId);
            }

            foreach ($place->getFeeds() as $feed) {
                $this->fetchFor(
                    $input,
                    [$feed],
                    $this->feedDataProviderFactory->fromFeed($feed)
                );
            }
        } elseif ($feedId = $input->getOption('feedid')) {
            if (!$feed = $this->feedRepository->find($feedId)) {
                throw new \Exception("Can't find a feed for id : " . $feedId);
            }
            $this->fetchFor(
                $input,
                [$feed],
                $this->feedDataProviderFactory->fromFeed($feed)
            );
        } else {
            foreach ($this->feedDataProviderFactory::listProviders() as $feedDataProviderType) {
                // We fetch all Feeds data.
                if ($feeds = $this->feedRepository->findAllActive($feedDataProviderType)) {
                    $this->fetchFor(
                        $input,
                        $feeds,
                        $this->feedDataProviderFactory->fromFeeds($feeds)
                    );
                }
            }
        }

        return 0;
    }

    private function fetchFor(InputInterface $input, array $feeds, FeedDataProviderInterface $feedDataProvider)
    {
        if ($date = $input->getOption('date')) {
            $date = new \DateTimeImmutable($date);

            $errors = $feedDataProvider->fetchDataFor($date, $feeds, $input->getOption('force'));
        } elseif (($startDate = $input->getOption('startDate')) && ($endDate = $input->getOption('endDate'))) {
            $startDate = new \DateTimeImmutable($startDate);
            $endDate = new \DateTimeImmutable($endDate);

            $errors = $feedDataProvider->fetchDataBetween($startDate, $endDate, $feeds, $input->getOption('force'));
        } else {
            // Else we update from last data to last available data.
            $errors = $feedDataProvider->fetchDataUntilLastUpdateTo($feeds);
        }
    }
}
