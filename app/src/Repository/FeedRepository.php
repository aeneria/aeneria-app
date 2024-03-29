<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Feed;
use App\Entity\FeedData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Feed>
 *
 * @method Feed|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feed|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feed[]    findAll()
 * @method Feed[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedRepository extends ServiceEntityRepository
{
    /** @var FeedDataRepository */
    private $feedDataRepository;

    public function __construct(ManagerRegistry $registry, FeedDataRepository $feedDataRepository)
    {
        parent::__construct($registry, Feed::class);

        $this->feedDataRepository = $feedDataRepository;
    }

    /**
     * Create and persist Feed dependent FeedData according to it type.
     */
    public function createDependentFeedData(Feed $feed): void
    {
        // We check, for this feed, if each dataFeeds are already created,
        // and create it if not.
        foreach (Feed::getDataTypeFor($feed->getFeedType()) as $label) {
            $feedData = $this->feedDataRepository->findOneBy([
                'feed' => $feed,
                'dataType' => $label,
            ]);

            if (!$feedData) {
                $feedData = new FeedData();
                $feedData->setDataType($label);
                $feedData->setFeed($feed);
                $this->getEntityManager()->persist($feedData);
            }
        }
    }

    /**
     * Remove ALL data (feedData and dataValue) for a feed and then feed itself
     */
    public function purge(Feed $feed)
    {
        foreach ($this->feedDataRepository->findByFeed($feed) as $feedData) {
            $this->feedDataRepository->purge($feedData);
        }

        $this
            ->createQueryBuilder('f')
            ->delete()
            ->where('f.id = :id')
            ->setParameter('id', $feed->getId())
            ->getQuery()
            ->execute()
        ;
    }

    public function findAllActive($feedDataProviderType = null)
    {
        $queryBuilder = $this
            ->createQueryBuilder('f', 'f.id')
            ->select()
            ->innerJoin('f.places', 'p')
            ->innerJoin('p.user', 'u')
            ->where('u.active = 1')
        ;

        if ($feedDataProviderType) {
            $queryBuilder
                ->where('f.feedDataProviderType = :type')
                ->setParameter('type', $feedDataProviderType)
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get Date of last up to date data.
     */
    public function getLastUpToDate(array $feeds): ?\DateTime
    {
        // Get all feedData.
        $feedDataList = $this->feedDataRepository->findByFeed($feeds);

        // Foreach feedData we get the last up to date value.
        /** @var \App\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            // A feed is up to date only if one feedData is up to date.
            // Well it could be that we will never have some feedData for a Feed. (in particular nebulosity from meteofrance)
            // In this case, if we choose that a feed is up to date only if all its feedData
            // are up to date, we will try to get this missing data over and over and flood the api
            // of the feed AND that's not cool :( and we try to be cool people :)

            $feedDataLastUpToDate = $this->feedDataRepository->getLastUpToDate($feedData);

            if (empty($lastUpToDate)) {
                $lastUpToDate = $feedDataLastUpToDate;
            }

            $lastUpToDate = \max($lastUpToDate, $feedDataLastUpToDate);
        }

        // If we have no data, we start 2 weeks ago
        if (empty($lastUpToDate)) {
            $lastUpToDate = new \DateTime("2 weeks ago");
        }

        return $lastUpToDate->add(new \DateInterval('P1D'));
    }

    /**
     * Check if there's data in DB for $date for all $feed's feedData and for all $frequencies.
     */
    public function isUpToDate(Feed $feed, \DateTimeImmutable $date, array $frequencies): bool
    {
        // Get all feedData.
        $feedDataList = $this->feedDataRepository->findByFeed($feed);

        $isUpToDate = true;

        // Foreach feedData we check if we have a value for yesterday.
        /** @var \App\Entity\FeedData $feedData */
        foreach ($feedDataList as $feedData) {
            // A feed is up to date only if all its feedData are up to date.
            $isUpToDate = $isUpToDate && $this->feedDataRepository->isUpToDate($feedData, $date, $frequencies);
        }

        return $isUpToDate;
    }

    /**
     * To avoid to create several feeds for the same station, this function check
     * if a feed already exists for param and then return it if one was found or
     * create one.
     */
    public function getOrCreateMeteoFranceFeed($param): Feed
    {
        // Try to find corresponding feed
        $meteoFranceFeed = $this->findOneBy([
            'name' => $param['STATION_ID'],
            'feedType' => Feed::FEED_TYPE_METEO,
            'feedDataProviderType' => Feed::FEED_DATA_PROVIDER_METEO_FRANCE,
        ]);

        if (!$meteoFranceFeed) {
            // Or create it
            $meteoFranceFeed = new Feed();
            $meteoFranceFeed
                ->setFeedType(Feed::FEED_TYPE_METEO)
                ->setFeedDataProviderType(Feed::FEED_DATA_PROVIDER_METEO_FRANCE)
                ->setName((string) $param['STATION_ID'])
                ->setParam($param)
            ;
            $this->createDependentFeedData($meteoFranceFeed);

            $this->getEntityManager()->persist($meteoFranceFeed);
            $this->getEntityManager()->flush();
        }

        return $meteoFranceFeed;
    }

    /**
     * Get Feeds associate with no Places
     *
     * @return Feed[]
     */
    public function findOrphans(): array
    {
        return $this->createQueryBuilder('f', 'f.id')
            ->select('f')
            ->leftJoin('f.places', 'p')
            ->where('p.id IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }
}
