<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Points;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function getUserTotalPoints(User $user): int
    {
        $points = $this->createQueryBuilder('p')
            ->select(['COALESCE(SUM(p.points), 0)'])
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $points;
    }
}
