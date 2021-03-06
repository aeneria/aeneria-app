<?php

namespace App\Repository;

use App\Entity\DataValue;
use App\Entity\FeedData;
use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FeedData|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedData|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedData[]    findAll()
 * @method FeedData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * Get Date of last up to date data.
     * @param EntityManager $entityManager
     * @param $frequencies array of int from DataValue frequencies
     *
     * @return \Datetime
     */
    public function getLastUpToDate(FeedData $feedData)
    {
        // Try to get the corresponding DataValue.
        $result = $this->dataValueRepository->getLastValue($feedData, DataValue::FREQUENCY['DAY']);

        if (!empty($result[0]['date'])) {
            return new \DateTime($result[0]['date']);
        }

        return null;
    }

    /**
     * Check if there's data in DB for $date for all $frequencies.
     * @param EntityManager $entityManager
     * @param \DateTime $date
     * @param $frequencies array of int from DataValue frequencies
     */
    public function isUpToDate(FeedData $feedData, \DateTimeImmutable $date, array $frequencies)
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
