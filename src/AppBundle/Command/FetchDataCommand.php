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
            $callback = DataApiController::FEED_TYPES[$feed->getFeedType()]['FETCH_CALLBACK'];
            $this->$callback($feed);
        }        
    }
    
    private function fetchLinkyData(Feed $feed) 
    {
        // Getting yesterday
        $today = new DateTime('NOW');
        $yesterday = $today->sub(new DateInterval('P1D'));
        
        // Declare the Linky object.
        $param = $feed->getParam();
        $linky = new Linky($param['LOGIN'], $param['PASSWORD']);
        
        // We get the corresponding dataFeed.
        $feedData = $this->entityManager->getRepository('AppBundle:FeedData')->findByFeed($feed);
        
        // Getting hour consumption data
        $this->fetchLinkyDataHour($feedData, $linky, $yesterday);
        
    }
    
    private function fetchLinkyDataHour(FeedData $feedData, Linky $linky, DateTime $date)
    {
        // We fetch data from last update until now for each frequency
        $datas = $linky->getData_perhour($date->format('d/m/Y'));
        
        // Storing data
        Foreach ($datas as $data){
            // Création de l'entité
            $dataValue = new DataValue();
            $dataValue->setFeedData($feedData);
            $dataValue->setFrequency(DataApiController::FREQUENCY['HOUR']);
            $dataValue->setValue($data['valeur']);
            $dataValue->setDate($data['date']);
            $dataValue->setHour($data['date']);
            $dataValue->setWeekDay($data['date']);
            
            // Étape 1 : On « persiste » l'entité
            $this->entityManager->persist($dataValue);
        }     
        
        // Flush all persisted DataValue
        $this->entityManager->flush();
    }
    
    private function fetchMeteoFranceData(Feed $feed) 
    {
        
    }
}