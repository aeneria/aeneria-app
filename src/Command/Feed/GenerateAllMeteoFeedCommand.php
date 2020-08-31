<?php

namespace App\Command\Feed;

use App\Entity\Place;
use App\Repository\FeedRepository;
use App\Repository\UserRepository;
use App\Services\FeedDataProvider\MeteoFranceDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate All MeteoFrance Feeds for a user
 */
class GenerateAllMeteoFeedCommand extends Command
{
    private $feedRepository;
    private $meteoFranceDataProvider;
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        FeedRepository $feedRepository,
        MeteoFranceDataProvider $meteoFranceDataProvider
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->feedRepository = $feedRepository;
        $this->meteoFranceDataProvider = $meteoFranceDataProvider;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:feed:meteo:generate-all')
            ->setDescription('Generate All MeteoFrance Feeds for a user')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        if (!$user = $this->userRepository->findOneByUsername($input->getArgument('username'))) {
            $this->io->error("User can't be found.");

            return 1;
        }

        $place = (new Place())
            ->setIcon('globe-europe')
            ->setPublic(false)
            ->setName('Meteo France Feeds')
            ->setUser($user)
        ;

        $feeds = [];
        foreach ($this->meteoFranceDataProvider->getAvailableStations() as $stationName => $stationId) {
            $feeds[] = $this->feedRepository->getOrCreateMeteoFranceFeed([
                'STATION_ID' => $stationId,
                'CITY' => $stationName,
            ]);
        }

        $place->setFeeds($feeds);
        $this->entityManager->persist($place);
        $this->entityManager->flush();

        $this->io->success('All MeteoFrance feeds has been successfully created.');

        return 0;
    }
}
