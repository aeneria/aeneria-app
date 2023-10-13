<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DataValue;
use App\Entity\Feed;
use App\Entity\FeedData;
use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedData>
 *
 * @method FeedData|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedData|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedData[]    findAll()
 * @method FeedData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method FeedData[]    findByFeed(Feed|Feed[] $feed)
 * @method FeedData    findOneByFeed(Feed $feed)
 */
class FeedDataRepository extends ServiceEntityRepository
{
    /** @var DataValueRepository */
    private $dataValueRepository;

    public function __construct(ManagerRegistry $registry, DataValueRepository $dataValueRepository)
    {
        parent::__construct($registry, FeedData::class);

        $this->dataValueRepository = $dataValueRepository;
    }

    /**
     * Remove ALL data (feedData and dataValue) for a feed and then feed itself
     */
    public function purge(FeedData $feedData)
    {
        $this->dataValueRepository
            ->createQueryBuilder('v')
            ->delete()
            ->where('v.feedData = :id')
            ->setParameter('id', $feedData->getId())
            ->getQuery()
            ->execute()
        ;

        $this
            ->createQueryBuilder('f')
            ->delete()
            ->where('f.id = :id')
            ->setParameter('id', $feedData->getId())
            ->getQuery()
            ->execute()
        ;
    }

    public function findOneByPlaceAndDataType(Place $place, string $dataType): ?FeedData
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('fd');

        $queryBuilder
            ->select()
            ->innerJoin('fd.feed', 'f')
            ->innerJoin('f.places', 'p', 'WITH', 'p = :place')
            ->setParameter('place', $place)
            ->andWhere('fd.dataType = :dataType')
            ->setParameter('dataType', $dataType)
        ;

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string|string[] $dataTypes
     *
     * @return FeedData[]
     */
    public function findByPlaceAndDataType(Place $place, $dataTypes): ?array
    {
        $dataTypes = \is_array($dataTypes) ? $dataTypes : $dataTypes;

        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('fd');

        $queryBuilder
            ->select()
            ->innerJoin('fd.feed', 'f')
            ->innerJoin('f.places', 'p', 'WITH', 'p = :place')
            ->setParameter('place', $place)
            ->andWhere($queryBuilder->expr()->in('fd.dataType', $dataTypes))
            // ->andWhere('fd.dataType in (:dataType)')
            // ->setParameter('dataType', \implode(',', \array_map(function($type) {return "'".$type."'";},$dataTypes)))
        ;

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get Date of last up to date data.
     */
    public function getLastUpToDate(FeedData $feedData): ?\DateTime
    {
        // Try to get the corresponding DataValue.
        $result = $this->dataValueRepository->getLastValue($feedData, DataValue::FREQUENCY_DAY);

        if (!empty($result[0]['date'])) {
            return new \DateTime($result[0]['date']);
        }

        return null;
    }

    /**
     * Check if there's data in DB for $date for all $frequencies.
     * @param int[] $frequencies array of int from DataValue frequencies
     */
    public function isUpToDate(FeedData $feedData, \DateTimeImmutable $date, array $frequencies): bool
    {
        $isUpToDate = true;

        // Foreach frequency we check if we have a value for date.
        foreach ($frequencies as $frequency) {
            $criteria = [
                'feedData' => $feedData,
                'date' => DataValue::adaptToFrequency($date, $frequency),
                'frequency' => $frequency,
            ];

            // Try to get the corresponding DataValue.
            $dataValue = $this->dataValueRepository->findBy($criteria);

            // A feed is up to date only if all its feedData are up to date.
            $isUpToDate = $isUpToDate && !empty($dataValue);
        }

        return $isUpToDate;
    }
}
