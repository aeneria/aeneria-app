<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PendingAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PendingAction>
 *
 * @method PendingAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method PendingAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method PendingAction[]    findAll()
 * @method PendingAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PendingActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PendingAction::class);
    }

    /**
     * @return PendingAction[]
     */
    public function findExpiredActions(): iterable
    {
        return $this
            ->createQueryBuilder('a', 'a.id')
            ->select()
            ->where('a.expirationDate <= :date')
            ->setParameter('date', new \DateTimeImmutable())
            ->getQuery()
            ->getResult()
        ;
    }
}
