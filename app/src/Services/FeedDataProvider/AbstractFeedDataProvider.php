<?php

declare(strict_types=1);

namespace App\Services\FeedDataProvider;

use App\Entity\Feed;
use App\Model\FetchingError;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use App\Services\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractFeedDataProvider implements FeedDataProviderInterface
{
    public const FETCH_STRATEGY_GROUPED = 'grouped';
    public const FETCH_STRATEGY_ONE_BY_ONE = 'one_by_one';

    public const ERROR_FETCH = 'FETCH_ERROR';
    public const ERROR_CONSENT = 'CONSENT_ERROR';

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var FeedRepository */
    protected $feedRepository;

    /** @var FeedDataRepository */
    protected $feedDataRepository;

    /** @var DataValueRepository */
    protected $dataValueRepository;

    /** @var NotificationService */
    protected $notificationService;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        NotificationService $notificationService,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->feedRepository = $feedRepository;
        $this->feedDataRepository = $feedDataRepository;
        $this->dataValueRepository = $dataValueRepository;

        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }

    /**
     * Get fetch strategy: Does feeds data should be fetched by group or one by one ?
     */
    protected function getFetchStrategy(): string
    {
        throw new \Exception("Your custom feedDataProvider should implement this method !");
    }

    /**
     * {@inheritdoc}
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false): array
    {
        throw new \Exception("Your custom feedDataProvider should implement this method !");
    }

    /**
     * {@inheritdoc}
     */
    public static function getParametersName(Feed $feed): array
    {
        throw new \Exception("Your custom feedDataProvider should implement this method !");
    }

    /**
     * Est-ce que la date donnée correspond à une donnée disponible.
     *
     * (dépend des règles métiers de chaque implémentation de FeedDataProvider)
     */
    abstract public static function isAvailableDataDate(\DateTimeImmutable $date): bool;

    /**
     * {@inheritdoc}
     */
    final public function fetchDataUntilLastUpdateTo(array $feeds): array
    {
        $errors = [];

        if (self::FETCH_STRATEGY_GROUPED === $this->getFetchStrategy()) {
            $lastUpToDate = $this->feedRepository->getLastUpToDate($feeds);
            $lastUpToDate = new \DateTime($lastUpToDate->format("Y-m-d"));

            while ($this->isAvailableDataDate(\DateTimeImmutable::createFromMutable($lastUpToDate))) {
                $errors = \array_merge(
                    $errors,
                    $this->fetchData(\DateTimeImmutable::createFromInterface($lastUpToDate), $feeds)
                );

                $lastUpToDate->add(new \DateInterval('P1D'));
            }
        } elseif (self::FETCH_STRATEGY_ONE_BY_ONE === $this->getFetchStrategy()) {
            foreach ($feeds as $feed) {
                $lastUpToDate = $this->feedRepository->getLastUpToDate([$feed]);
                $lastUpToDate = new \DateTime($lastUpToDate->format("Y-m-d"));

                while ($this->isAvailableDataDate(\DateTimeImmutable::createFromMutable($lastUpToDate))) {
                    $errors = \array_merge(
                        $errors,
                        $this->fetchData(\DateTimeImmutable::createFromInterface($lastUpToDate), [$feed])
                    );

                    $lastUpToDate->add(new \DateInterval('P1D'));
                }
            }
        } else {
            throw new \Exception(\sprintf("Strategy '%s' is unkown", $this->getFetchStrategy()));
        }

        return $this->handleErrors($errors);
    }

    /**
     * {@inheritdoc}
     */
    final public function fetchDataFor(\DateTimeImmutable $date, array $feeds, bool $force): array
    {
        $errors = $this->fetchData($date, $feeds, $force);

        return $this->handleErrors($errors);
    }

    /**
     * {@inheritdoc}
     */
    final public function fetchDataBetween(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, array $feeds, bool $force): array
    {
        $errors = [];

        $date = \DateTime::createFromImmutable($startDate);
        while ($date <= $endDate) {
            $errors = \array_merge(
                $errors,
                $this->fetchDataFor(\DateTimeImmutable::createFromMutable($date), $feeds, $force)
            );
            $date->add(new \DateInterval('P1D'));
        }

        return $this->handleErrors($errors);
    }

    /**
     * @param FetchingError[] $errors
     */
    private function handleErrors(array $errors): array
    {
        if ($errors) {
            foreach ($errors as $error) {
                $feed = $error->getFeed();
                $feed->addFetchError();
                $this->entityManager->persist($feed);
                $this->entityManager->flush();
            }
        }

        return $errors;
    }
}
