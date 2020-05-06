<?php

namespace App\Repository;

use App\Entity\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Test|null find($id, $lockMode = null, $lockVersion = null)
 * @method Test|null findOneBy(array $criteria, array $orderBy = null)
 * @method Test[]    findAll()
 * @method Test[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Test::class);
    }

    public function getNearTests(Test $test): array
    {
        $prevTest = $this->createQueryBuilder('test')
            ->select('test.id')
            ->andWhere('IDENTITY(test.city) = :city_id')
            ->andWhere('test.id < :test_id')
            ->setParameters([
                'city_id' => $test->getCity()->getId(),
                'test_id' => $test->getId(),
            ])
            ->orderBy('test.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $nextTest = $this->createQueryBuilder('test')
            ->select('test.id')
            ->andWhere('IDENTITY(test.city) = :city_id')
            ->andWhere('test.id > :test_id')
            ->setParameters([
                'city_id' => $test->getCity()->getId(),
                'test_id' => $test->getId(),
            ])
            ->orderBy('test.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return [
            'prev' => $prevTest ? $prevTest['id'] : null,
            'next' => $nextTest ? $nextTest['id'] : null,
        ];
    }
}
