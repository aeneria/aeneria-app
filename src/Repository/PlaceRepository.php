<?php

namespace App\Repository;

use App\Entity\Feed;
use App\Entity\Place;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Place|null find($id, $lockMode = null, $lockVersion = null)
 * @method Place|null findOneBy(array $criteria, array $orderBy = null)
 * @method Place[]    findAll()
 * @method Place[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceRepository extends ServiceEntityRepository
{
    /** @var bool */
    private $userCanSharePlace;

    /** @var bool */
    private $placeCanBePublic;

    /** @var FeedRepository */
    private $feedRepository;

    public function __construct(bool $userCanSharePlace, bool $placeCanBePublic, ManagerRegistry $registry, FeedRepository $feedRepository)
    {
        $this->userCanSharePlace = $userCanSharePlace;
        $this->placeCanBePublic = $placeCanBePublic;
        $this->feedRepository = $feedRepository;

        parent::__construct($registry, Place::class);
    }

    /**
     * Remove ALL data (feed, feedData and dataValue) for a place and then place itself
     */
    public function purge(Place $place)
    {
        foreach ($place->getFeeds() as $feed) {
            \assert($feed instanceof Feed);

            // Halt ! A feed can be attached to several places !
            // We have to check that before we purge it !
            $feedPlaces = $feed->getPlaces();
            if (\count($feedPlaces) <= 1 && $feedPlaces[0] == $place) {
                $this->feedRepository->purge($feed);
            }
        }

        $this
            ->createQueryBuilder('p')
            ->delete()
            ->where('p.id = :id')
            ->setParameter('id', $place->getId())
            ->getQuery()
            ->execute()
        ;
    }

    public function getAllowedPlaces(User $user)
    {
        $queryBuilder = $this
            ->createQueryBuilder('p', 'p.id')
            ->select()
            ->orWhere('p.user = :user')
        ;

        if ($this->userCanSharePlace) {
            $queryBuilder->orWhere(':user MEMBER OF p.allowedUsers');
        }

        if ($this->placeCanBePublic) {
            $queryBuilder->orWhere('p.public = true');
        }

        return $queryBuilder
            ->setParameter('user', $user)
            ->orderBy('p.name', 'asc')
            ->getQuery()
            ->getResult()
        ;
    }
}
