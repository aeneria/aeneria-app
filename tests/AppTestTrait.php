<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\PendingAction;
use App\Entity\Place;
use App\Entity\User;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use App\Repository\PendingActionRepository;
use App\Repository\PlaceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

trait AppTestTrait
{
    private $_entityManager;
    private $_logger;

    final protected static function getResourceDir(): string
    {
        return __DIR__ . '/Resources';
    }

    final protected function getEntityManager(): EntityManagerInterface
    {
        return $this->_entityManager ?? (
            $this->_entityManager = $this->getContainer()->get('doctrine')->getManager()
        );
    }

    final protected function getLogger(): LoggerInterface
    {
        return $this->_logger ?? (
            $this->_logger = new NullLogger()
        );
    }

    final protected function getParameter(string $parameter): string
    {
        return $this->getContainer()->getParameter($parameter);
    }

    final protected function getUserRepository(): UserRepository
    {
        return $this->getEntityManager()->getRepository(User::class);
    }

    final protected function getPlaceRepository(): PlaceRepository
    {
        return $this->getEntityManager()->getRepository(Place::class);
    }

    final protected function getFeedRepository(): FeedRepository
    {
        return $this->getEntityManager()->getRepository(Feed::class);
    }

    final protected function getFeedDataRepository(): FeedDataRepository
    {
        return $this->getEntityManager()->getRepository(FeedData::class);
    }

    final protected function getDataValueRepository(): DataValueRepository
    {
        return $this->getEntityManager()->getRepository(DataValue::class);
    }

    final protected function getPendingActionRepository(): PendingActionRepository
    {
        return $this->getEntityManager()->getRepository(PendingAction::class);
    }

    final protected function getPassordEncoder(): UserPasswordEncoderInterface
    {
        return $this->getContainer()->get('security.password_encoder');
    }

    final protected function getSerializer(): SerializerInterface
    {
        return $this->getContainer()->get('serializer');
    }

    /**
     * Create User from array
     */
    final protected function createUser(array $data = null): User
    {
        return (new User())
            ->setUsername($data['username'] ?? 'test' . \rand() . '@example.com')
            ->setPassword($data['password'] ?? 'password' . \rand())
            ->setActive($data['active'] ?? true)
            ->setRoles($data['roles'] ?? [User::ROLE_USER])
            ->setPlaces($data['places'] ?? [])
            ->setSharedPlaces($data['sharedPlaces'] ?? [])
        ;
    }

    final protected function createPersistedUser(array $data = null): User
    {
        $user = $this->createUser($data);
        $this->getEntityManager()->persist($user);

        return $user;
    }

    /**
     * Create Place from array
     */
    final protected function createPlace(array $data = []): Place
    {
        return (new Place())
            ->setId($data['id'] ?? \rand())
            ->setName($data['name'] ?? 'test' . \rand())
            ->setIcon($data['icon'] ?? 'home')
            ->setPublic($data['public'] ?? false)
            ->setUser($data['user'] ?? $this->createUser())
            ->setAllowedUsers($data['allowedUsers'] ?? [])
            ->setFeeds($data['feeds'] ?? [])
        ;
    }

    final protected function createPersistedPlace(array $data = [], array $userData = []): Place
    {
        $user = $data['user'] ?? $this->createPersistedUser($userData);
        $place = $this->createPlace($data + ['user' => $user]);
        $this->getEntityManager()->persist($place);

        return $place;
    }

    /**
     * Create Feed from array
     */
    final protected function createFeed(array $data = []): Feed
    {
        return (new Feed())
            ->setId($data['id'] ?? \rand())
            ->setName($data['name'] ?? 'test' . \rand())
            ->setFeedType($data['feedType'] ?? Feed::FEED_TYPE_ELECTRICITY)
            ->setFeedDataProviderType($data['feedDataProviderType'] ?? Feed::FEED_DATA_PROVIDER_FAKE)
            ->setParam($data['param'] ?? [])
            ->setPlaces($data['places'] ?? [])
        ;
    }

    final protected function createPersistedFeed(array $data = [], array $placeData = [], array $userData = []): Feed
    {
        $place = $data['place'] ?? $this->createPersistedPlace($placeData, $userData);
        $feed = $this->createFeed($data + ['place' => $place]);
        if ($place->getId()) {
            $place->addFeed($feed);
        }
        $this->getEntityManager()->persist($feed);

        return $feed;
    }

    /**
     * Create FeedData from array
     */
    final protected function createFeedData(array $data = []): FeedData
    {
        return (new FeedData())
            ->setId($data['id'] ?? \rand())
            ->setFeed($data['feed'] ?? $this->createFeed())
            ->setDataType($data['dataType'] ?? FeedData::FEED_DATA_CONSO_ELEC)
        ;
    }

    final protected function createPersistedFeedData(array $data = [], array $feedData = [], array $placeData = [], array $userData = []): FeedData
    {
        $feed = $data['feed'] ?? $this->createPersistedFeed($feedData, $placeData, $userData);
        $feedData = $this->createFeedData($data + ['feed' => $feed]);
        $this->getEntityManager()->persist($feedData);

        return $feedData;
    }

    /**
     * Create DataValue from array
     */
    final protected function createDataValue(array $data = []): DataValue
    {
        return (new DataValue())
            ->setId($data['id'] ?? \rand())
            ->setFeedData($data['feedData'] ?? $this->createFeedData())
            ->setFrequency(DataValue::FREQUENCY_HOUR)
            ->setValue($data['value'] ?? 12)
            ->setDate($data['date'] ?? new \DateTimeImmutable())
            ->updateDateRelatedData()
        ;
    }

    final protected function createPersistedDataValue(array $data = [], array $feedDataData = [], array $feedData = [], array $placeData = [], array $userData = []): DataValue
    {
        $feedData = $data['feedData'] ?? $this->createPersistedFeedData($feedDataData, $feedData, $placeData, $userData);
        $dataValue = $this->createDataValue($data + ['feedData' => $feedData]);
        $this->getEntityManager()->persist($dataValue);

        return $dataValue;
    }

    /**
     * Create PendingAction from array
     */
    final protected function createPendingAction(array $data = []): PendingAction
    {
        return (new PendingAction())
            ->setId($data['id'] ?? \rand())
            ->setToken($data['token'] ?? 'token' . \rand())
            ->setUser($data['user'] ?? $this->createUser())
            ->setAction($data['action'] ?? 'action')
            ->setExpirationDate($data['expirationDate'] ?? new \DateTimeImmutable())
            ->setParam($data['param'] ?? ['testParam' => 'testValue'])
        ;
    }

    final protected function createPersistedPendingAction(array $data = [], array $userData = []): PendingAction
    {
        $user = $data['user'] ?? $this->createPersistedUser($userData);
        $pendingAction = $this->createPendingAction($data + ['user' => $user]);
        $this->getEntityManager()->persist($pendingAction);

        return $pendingAction;
    }
}
