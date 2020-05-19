<?php

namespace App\Repository;

use App\Entity\TestAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestAction[]    findAll()
 * @method TestAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestAction::class);
    }

    // /**
    //  * @return TestAction[] Returns an array of TestAction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TestAction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
