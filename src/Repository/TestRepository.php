<?php

namespace App\Repository;

use App\Entity\Points;
use App\Entity\PointsType;
use App\Entity\Test;
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

    public function getAllTests(User $user): array
    {
        $pointsRepository = $this->getEntityManager()->getRepository(Points::class);
        $finishedTests = $pointsRepository->getFinishedTests($user);

        $tests = $this->createQueryBuilder('test')
            ->select(['test.id', 'test.imageUrl as image_url'])
            ->getQuery()
            ->getResult();

        foreach ($tests as $key => $test) {
            $tests[$key]['type'] = $finishedTests[$test['id']] ?? '';
        }

        return $tests;
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

    // TODO: переделать. Теперь статус есть теста и статус ответа.
    public function getStatus(User $user, Test $test): string
    {
        $pointsRepository = $this->getEntityManager()->getRepository(Points::class);

        $finishedStatus = $pointsRepository->getFinishedStatus($user, $test);

        return $finishedStatus ?? Test::STATUS_IN_PROCESSING;
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
}
