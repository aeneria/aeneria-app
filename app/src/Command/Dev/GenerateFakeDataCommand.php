<?php

declare(strict_types=1);

namespace App\Command\Dev;

use App\Entity\Feed;
use App\Entity\Place;
use App\Entity\User;
use App\Repository\FeedRepository;
use App\Repository\PlaceRepository;
use App\Repository\UserRepository;
use App\Services\FeedDataProvider\FakeDataProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Defined command to refresh all feeds
 */
class GenerateFakeDataCommand extends Command
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UserRepository */
    private $userRepository;
    /** @var PlaceRepository */
    private $placeRepository;
    /** @var FeedRepository */
    private $feedRepository;

    /** @var FakeDataProvider */
    private $fakeDataProvider;

    /** @var UserPasswordHasherInterface */
    private $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        PlaceRepository $placeRepository,
        FeedRepository $feedRepository,
        FakeDataProvider $fakeDataProvider,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;

        $this->userRepository = $userRepository;
        $this->placeRepository = $placeRepository;
        $this->feedRepository = $feedRepository;
        $this->fakeDataProvider = $fakeDataProvider;

        $this->passwordHasher = $passwordHasher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:dev:generate-fake-data')
            ->setDescription('Generate fake data for development')
            ->setHelp('Create a User and a Place and generate fake data for it.')
            ->addOption('user-name', null, InputOption::VALUE_OPTIONAL, "A name for the user (default 'user-test').")
            ->addOption('user-password', null, InputOption::VALUE_OPTIONAL, "A name for the user (default 'password').")
            ->addOption('place-name', null, InputOption::VALUE_OPTIONAL, "A name for the place (default 'place-test').")
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, "Data will be created from this date (default '3 months ago').")
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, "Data will be created to this date (default 'today').")
            ->addOption('force', null, InputOption::VALUE_NONE, "Erase and rewrite data if there're already ones.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getOption('user-name') ?? 'user-test';
        $password = $input->getOption('user-password') ?? 'password';
        $placeName = $input->getOption('place-name') ?? 'place-test';
        $from = $input->getOption('from') ? new \DateTimeImmutable($input->getOption('from')) : new \DateTimeImmutable('3 months ago midnight');
        $from = \DateTimeImmutable::createFromFormat('!Y-m-d', $from->format('Y-m-d'));
        $to = $input->getOption('to') ? new \DateTimeImmutable($input->getOption('to')) : new \DateTimeImmutable('today midnight');
        $to = \DateTimeImmutable::createFromFormat('!Y-m-d', $to->format('Y-m-d'));
        $force = $input->getOption('force');

        $user = $this->createOrUpdateUser($username, $password);
        $place = $this->createOrGetPlace($user, $placeName);

        $this->fakeDataProvider->fetchDataBetween($from, $to, \iterator_to_array($place->getFeeds()), $force);

        return 0;
    }

    private function createOrUpdateUser(string $username, string $password): User
    {
        if (!$user = $this->userRepository->findOneByUsername($username)) {
            $user = new User();
            $user->setUsername($username);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setActive(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createOrGetPlace(User $user, string $placeName): Place
    {
        $criteria = [
            'user' => $user,
            'name' => $placeName,
        ];

        if (!$place = $this->placeRepository->findOneBy($criteria)) {
            // First we create a Meteo and an electricity feed
            $meteoFeed = (new Feed())
                ->setName(Feed::FEED_TYPE_METEO)
                ->setFeedType(Feed::FEED_TYPE_METEO)
                ->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_FAKE)
                ->setParam([])
            ;

            $electricityFeed = (new Feed())
                ->setName(Feed::FEED_TYPE_ELECTRICITY)
                ->setFeedType(Feed::FEED_TYPE_ELECTRICITY)
                ->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_FAKE)
                ->setParam([])
            ;

            $gazFeed = (new Feed())
                ->setName(Feed::FEED_TYPE_GAZ)
                ->setFeedType(Feed::FEED_TYPE_GAZ)
                ->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_FAKE)
                ->setParam([])
            ;

            $place = (new Place())
                ->setName($placeName)
                ->setPublic(true)
                ->setUser($user)
                ->addFeed($meteoFeed)
                ->addFeed($electricityFeed)
                ->addFeed($gazFeed)
            ;

            $this->entityManager->persist($place);
            $this->entityManager->flush();

            foreach ($place->getFeeds() as $feed) {
                $this->entityManager->persist($feed);
                $this->feedRepository->createDependentFeedData($feed);
            }

            $this->entityManager->flush();
        }

        return $this->placeRepository->findOneById($place->getId());
    }
}
