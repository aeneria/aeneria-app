<?php

namespace App\Repository;

use App\Entity\Place;

/**
 * Place Repository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PlaceRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Remove ALL data (feed, feedData and dataValue) for a place and then place itself
     */
    public function purge(Place $place)
    {
        $feedRepository = $this
            ->getEntityManager()
            ->getRepository('App:Feed')
        ;

        foreach ($feedRepository->findByPlace($place) as $feed) {
            $feedRepository->purge($feed);

        }

        $this
            ->createQueryBuilder('f')
            ->delete()
            ->where('f.id = :id')
            ->setParameter('id', $place->getId())
            ->getQuery()
            ->execute()
        ;
    }
}
