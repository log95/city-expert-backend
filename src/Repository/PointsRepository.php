<?php

namespace App\Repository;

use App\Entity\Points;
use App\Entity\PointsType;
use App\Entity\Test;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Points|null find($id, $lockMode = null, $lockVersion = null)
 * @method Points|null findOneBy(array $criteria, array $orderBy = null)
 * @method Points[]    findAll()
 * @method Points[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Points::class);
    }

    public function getHintIds(User $user, Test $test, PointsType $pointsType): array
    {
        $hints = $this->createQueryBuilder('points')
            ->select('IDENTITY(points.hint) as hint_id')
            ->andWhere('IDENTITY(points.user) = :user_id')
            ->andWhere('IDENTITY(points.test) = :test_id')
            ->andWhere('IDENTITY(points.type) = :type_id')
            ->setParameters([
                'user_id' => $user->getId(),
                'test_id' => $test->getId(),
                'type_id' => $pointsType->getId(),
            ])
            ->getQuery()
            ->getResult();

        $hints = new ArrayCollection($hints);
        $hintIds = $hints->map(fn($hint) => $hint['hint_id']);

        return $hintIds->getValues();
    }

    public function getFinishedTests(User $user): array
    {
        $tests = $this->createQueryBuilder('points')
            ->select(['IDENTITY(points.test) as test_id', 'points_type.name as points_type_name'])
            ->leftJoin('points.type', 'points_type')
            ->andWhere('points.user = :user')
            ->andWhere('points_type.name IN (:point_type_names)')
            ->setParameters([
                'user' => $user,
                'point_type_names' => [PointsType::CORRECT_ANSWER, PointsType::SHOW_ANSWER],
            ])
            ->getQuery()
            ->getResult();

        $result = [];

        foreach ($tests as $testInfo) {
            $result[$testInfo['test_id']] = $testInfo['points_type_name'];
        }

        return $result;
    }

    public function getFinishedStatus(User $user, Test $test): ?string
    {
        $testFinishedStatus = $this->createQueryBuilder('points')
            ->select(['points_type.name as status'])
            ->leftJoin('points.type', 'points_type')
            ->andWhere('points.user = :user')
            ->andWhere('points.test = :test')
            ->andWhere('points_type.name IN (:point_type_names)')
            ->setParameters([
                'user' => $user,
                'test' => $test,
                'point_type_names' => [PointsType::CORRECT_ANSWER, PointsType::SHOW_ANSWER],
            ])
            ->getQuery()
            ->getOneOrNullResult();

        return $testFinishedStatus ? $testFinishedStatus['status'] : null;
    }
}
