<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PointsType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PointsType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PointsType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PointsType[]    findAll()
 * @method PointsType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointsTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PointsType::class);
    }
}
