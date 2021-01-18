<?php

namespace App\Services\FeedDataProvider;

use App\Entity\Feed;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractFeedDataProvider implements FeedDataProviderInterface
{
    const FETCH_STRATEGY_GROUPED = 'grouped';
    const FETCH_STRATEGY_ONE_BY_ONE = 'one_by_one';

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var FeedRepository */
    protected $feedRepository;

    /** @var FeedDataRepository */
    protected $feedDataRepository;

    /** @var DataValueRepository */
    protected $dataValueRepository;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        FeedRepository $feedRepository,
        FeedDataRepository $feedDataRepository,
        DataValueRepository $dataValueRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->feedRepository = $feedRepository;
        $this->feedDataRepository = $feedDataRepository;
        $this->dataValueRepository = $dataValueRepository;

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

        return [];
    }

    /**
     * {@inheritdoc}
     */
    final public function fetchDataUntilLastUpdateTo(\DateTimeImmutable $date, array $feeds): array
    {
        $errors = [];

        if (self::FETCH_STRATEGY_GROUPED === $this->getFetchStrategy()) {
            $lastUpToDate = $this->feedRepository->getLastUpToDate($feeds);
            $lastUpToDate = new \DateTime($lastUpToDate->format("Y-m-d 00:00:00"));

            while ($lastUpToDate <= $date) {
                $errors = \array_merge(
                    $errors,
                    $this->fetchData(\DateTimeImmutable::createFromMutable($lastUpToDate), $feeds)
                );
                $lastUpToDate->add(new \DateInterval('P1D'));
            }
        } elseif (self::FETCH_STRATEGY_ONE_BY_ONE === $this->getFetchStrategy()) {
            foreach ($feeds as $feed) {
                $lastUpToDate = $this->feedRepository->getLastUpToDate([$feed]);
                $lastUpToDate = new \DateTime($lastUpToDate->format("Y-m-d 00:00:00"));

                while ($lastUpToDate <= $date) {
                    $errors = \array_merge(
                        $errors,
                        $this->fetchData(\DateTimeImmutable::createFromMutable($lastUpToDate), [$feed])
                    );
                    $lastUpToDate->add(new \DateInterval('P1D'));
                }
            }
        } else {
            throw new \Exception(\sprintf("Strategy '%s' is unkown", $this->getFetchStrategy()));
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    final public function fetchDataFor(\DateTimeImmutable $date, array $feeds, bool $force): array
    {
        return $this->fetchData($date, $feeds, $force);
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

        return $errors;
    }
}
