<?php

namespace App\Repository;

use App\Entity\FeedData;
use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * FeedDataRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeedDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedData::class);
    }

    /**
     * Remove ALL data (feedData and dataValue) for a feed and then feed itself
     */
    public function purge(FeedData $feedData)
    {
        $dataValueRepository = $this
            ->getEntityManager()
            ->getRepository('App:DataValue')
        ;

        $dataValueRepository
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

    public function findOneByPlaceAndDataType(Place $place, string $dataType)
    {
        // Create the query builder
        $queryBuilder = $this->createQueryBuilder('fd');

        $queryBuilder->select()
            ->innerJoin('fd.feed', 'f')
            ->where('f.place = :place')
            ->setParameter('place', $place)
            ->andWhere('fd.dataType = :dataType')
            ->setParameter('dataType', $dataType)
        ;

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
