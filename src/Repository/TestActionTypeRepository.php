<?php

namespace App\Repository;

use App\Entity\TestActionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestActionType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestActionType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestActionType[]    findAll()
 * @method TestActionType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestActionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestActionType::class);
    }

    // /**
    //  * @return TestActionType[] Returns an array of TestActionType objects
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
    public function findOneBySomeField($value): ?TestActionType
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
