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
            // We fetch data if force option is true or if the feed is'nt up to date.
            if ($input->getArgument('force') || !$this->isFeedUpToDate($feed, $yesterday)) {
                $callback = Feed::FEED_TYPES[$feed->getFeedType()]['FETCH_CALLBACK'];
                $this->$callback($feed);
            }
        }
    }

    /**
     * Linky callback for fetching data.
     * @param Feed $feed
     */
    private function fetchLinkyData(Feed $feed)
    {
        $linky = new Linky($feed, $this->entityManager);
        $linky->fetchYesterdayData();
    }

    /**
     * MeteoFrance callback for fetching data.
     * @param Feed $feed
     */
    private function fetchMeteoFranceData(Feed $feed)
    {
        $meteoFrance = new MeteoFrance($feed, $this->entityManager);
        $meteoFrance->fetchYesterdayData();
    }

    /**
     * Check if there's data in DB for $date forall $feed's feedData.
     * @param Feed $feed
     * @param \DateTime $date
     */
    private function isFeedUpToDate(Feed $feed, \DateTime $date)
    {
        // Get all feedData.
        $feedDataList = $this->entityManager->getRepository('AppBundle:FeedData')->findByFeed($feed);

        $isUpToDate = TRUE;

        // Foreach feedData we check if we have a value for yesterday.
        /** @var \AppBundle\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            $criteria = [
                'feedData' => $feedData,
                'date' => $date,
            ];

            // Try to get the corresponding DataValue.
            $dataValue = $this->entityManager->getRepository('AppBundle:DataValue')->findBy($criteria);

            // A feed is up to date only if all its feedData are up to date.
            $isUpToDate = $isUpToDate && isset($dataValue);
        }

        return $isUpToDate;
    }
}
