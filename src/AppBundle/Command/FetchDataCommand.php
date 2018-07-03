<?php

namespace AppBundle\Command;

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
        // the name of the command (the part after "bin/console")
        ->setName('pilea:fetch-data')

        // the short description shown while running "php bin/console list"
        ->setDescription('Get daily data from all feed')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to fetch data from all defiend feeds')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // We fetch all Feeds data.
        $feeds = $this->entityManager->getRepository('AppBundle:Feed')->findAll();

        // For each feeds, we call the right method to fetch data.
        /** @var \AppBundle\Entity\Feed $feeds */
        foreach($feeds as $feed) {
            $callback = Feed::FEED_TYPES[$feed->getFeedType()]['FETCH_CALLBACK'];
            $this->$callback($feed);
        }
    }

    private function fetchLinkyData(Feed $feed)
    {
        $linky = new Linky($feed, $this->entityManager);
        $linky->fetchYesterdayData();
    }

    private function fetchMeteoFranceData(Feed $feed)
    {
        $meteoFrance = new MeteoFrance($feed, $this->entityManager);
        $meteoFrance->fetchYesterdayData();
    }
}
