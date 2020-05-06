<?php

namespace App\Repository;

use App\Entity\Points;
use App\Entity\PointsType;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
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

    public function getUsedHintIds(User $user, Test $test): array
    {
        $pointsTypeRepository = $this->getEntityManager()->getRepository(PointsType::class);
        /** @var PointsType $pointsType */
        $pointsType = $pointsTypeRepository->findOneBy(['name' => PointsType::HINT]);

        $pointsRepository = $this->getEntityManager()->getRepository(Points::class);

        return $pointsRepository->getHintIds($user, $test, $pointsType);
    }
}
