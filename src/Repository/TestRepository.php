<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\TestStatus;
use App\Entity\Points;
use App\Entity\PointsType;
use App\Entity\Test;
use App\Entity\TestAction;
use App\Entity\User;
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

    public function getTestListForModerator(User $moderator): array
    {
        return $this->createQueryBuilder('test')
            ->select(['test.id', 'test.currentStatus', 'test.imageUrl'])
            ->andWhere('test.moderator = :moderator')
            ->setParameter('moderator', $moderator)
            ->orderBy('test.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function getTestListForAccount(User $user): array
    {
        return $this->createQueryBuilder('test')
            ->select(['test.id', 'test.currentStatus', 'test.imageUrl'])
            ->andWhere('test.createdBy = :creator')
            ->setParameter('creator', $user)
            ->orderBy('test.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
}
