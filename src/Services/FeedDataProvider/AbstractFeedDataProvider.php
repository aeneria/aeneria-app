<?php
namespace App\Services\FeedDataProvider;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Repository\DataValueRepository;
use App\Repository\FeedDataRepository;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;

abstract class AbstractFeedDataProvider {

    protected $entityManager;
    protected $feedRepository;
    protected $feedDataRepository;
    protected $dataValueRepository;

    protected $httpClient;

    public function __construct(EntityManagerInterface $entityManager, FeedRepository $feedRepository, FeedDataRepository $feedDataRepository, DataValueRepository $dataValueRepository)
    {
        $this->entityManager = $entityManager;
        $this->feedRepository = $feedRepository;
        $this->feedDataRepository = $feedDataRepository;
        $this->dataValueRepository = $dataValueRepository;

        $this->httpClient = HttpClient::create();
    }

    /**
     * Fetch data for $date and for a array of feeds
     *
     * @param \Datetime $date
     */
    public function fetchData(\DateTimeImmutable $date, array $feeds, bool $force = false)
    {
        throw new \Exception("Your custom feedDataProvider should implement this method !");
    }

    /**
     * Get array parameters that a feed which uses this provider should have.
     */
    public static function getParametersName(Feed $feed): array
    {
        throw new \Exception("Your custom feedDataProvider should implement this method !");
        return [];
    }

    /**
     * Fetch data from last data to $date.
     */
    final public function fetchDataUntilLastUpdateTo(\DateTimeImmutable $date, array $feeds): void
    {
        $lastUpToDate = $this->feedRepository->getLastUpToDate($feeds);
        $lastUpToDate = new \DateTime($lastUpToDate->format("Y-m-d 00:00:00"));

        while($lastUpToDate <= $date) {
            $this->fetchData(\DateTimeImmutable::createFromMutable($lastUpToDate), $feeds);
            $lastUpToDate->add(new \DateInterval('P1D'));
        }
    }

    /**
     * Fetch data for $date,
     * if $force is set to true, update data even if there are already ones.
     */
    final public function fetchDataFor(\DateTimeImmutable $date, array $feeds, bool $force): void
    {
        $this->fetchData($date, $feeds, $force);
    }

    /**
     * Fetch data from startDate to $endDate,
     * if $force is set to true, update data even if there are already ones.
     */
    final public function fetchDataBetween(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, array $feeds, bool $force): void
    {
        $date = \DateTime::createFromImmutable($startDate);
        while ($date <= $endDate) {
            $this->fetchDataFor(\DateTimeImmutable::createFromMutable($date), $feeds, $force);
            $date->add(new \DateInterval('P1D'));
        }
    }
}
