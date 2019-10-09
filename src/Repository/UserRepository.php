<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function isLastAdmin(string $username)
    {
        $users =  $this->createQueryBuilder('u')
            ->where('u.username <> :username')
            ->setParameter('username', $username)
            ->andWhere('u.active = true')
            ->getQuery()
            ->getResult()
        ;

        foreach ($users as $user) {
            if (\in_array(User::ROLE_ADMIN, $user->getRoles())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove ALL data (place, feed, feedData and dataValue) for a user and then user himself
     */
    public function purge(User $user)
    {
        $placeRepository = $this
            ->getEntityManager()
            ->getRepository('App:Place')
        ;

        foreach ($placeRepository->findByUser($user) as $place) {
            $placeRepository->purge($place);

        }

        $this
            ->createQueryBuilder('u')
            ->delete()
            ->where('u.id = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->execute()
        ;
    }

    public function getUsersList(User $user = null)
    {
        $queryBuilder = $this
            ->createQueryBuilder('u')
            ->select('u.id, u.username as username')
            ->orderBy('u.username', 'ASC')
        ;

        if ($user) {
            $queryBuilder
                ->andWhere('u.id <> :id')
                ->setParameter('id', $user->getId())
            ;
        }

        return array_column($queryBuilder->getQuery()->getResult() ?? [], "id", 'username');
    }
}
