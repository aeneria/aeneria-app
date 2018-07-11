<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Controller\DataApiController;
use AppBundle\Entity\Feed;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Object\Linky;
use Symfony\Component\Validator\Constraints\DateTime;
use AppBundle\Entity\FeedData;
use AppBundle\Entity\DataValue;
use AppBundle\Object\MeteoFrance;

/**
 * Defined command to refresh all feeds
 * @todo Simplify, no need for callbacks, just make Linky and MeteoFrance implements a same interface
 *
 */
class FetchDataCommand extends ContainerAwareCommand
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager= $entityManager;

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
        ->addArgument('force', InputArgument::REQUIRED, 'Refresh yesterday data even if it already exists ?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get yesterday datetime.
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));
        $yesterday = new \DateTime($yesterday->format("Y-m-d 00:00:00"));

        // We fetch all Feeds data.
        $feeds = $this->entityManager->getRepository('AppBundle:Feed')->findAll();

        // For each feeds, we call the right method to fetch data.
        /** @var \AppBundle\Entity\Feed $feeds */
        foreach($feeds as $feed) {
            $callback = Feed::FEED_TYPES[$feed->getFeedType()]['FETCH_CALLBACK'];
            $this->$callback($feed, $input->getArgument('force'), $yesterday);
        }
    }

    /**
     * Linky callback for fetching data.
     * @param Feed $feed
     */
    private function fetchLinkyData(Feed $feed, $force, $date)
    {
        // We fetch data if force option is true or if (date('H') >= 9 and the feed isn't up to date).
        // The ENEDIS site isn't stable before 9am... and we want to be sure to have yesterday data..
        dump(date('H'));
        dump($date);
        dump($feed->isFeedUpToDate($this->entityManager, $date, DataValue::FREQUENCY));
        if ($force || (date('H') >= 9 && !$feed->isFeedUpToDate($this->entityManager, $date, DataValue::FREQUENCY)) ) {
            $linky = new Linky($feed, $this->entityManager);
            $linky->fetchYesterdayData();
        }
    }

    /**
     * MeteoFrance callback for fetching data.
     * @param Feed $feed
     */
    private function fetchMeteoFranceData(Feed $feed, $force, $date)
    {
        // We fetch data if force option is true or if the feed isn't up to date.
        if ($force || !$feed->isFeedUpToDate($this->entityManager, $date, MeteoFrance::FREQUENCY)) {
            $meteoFrance = new MeteoFrance($feed, $this->entityManager);
            $meteoFrance->fetchYesterdayData();
        }
    }
}
