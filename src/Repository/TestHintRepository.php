<?php

namespace App\Repository;

use App\Entity\TestHint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestHint|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestHint|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestHint[]    findAll()
 * @method TestHint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestHintRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestHint::class);
    }
}
