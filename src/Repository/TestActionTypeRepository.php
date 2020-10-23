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

    public static function getFinishedTypesName(): array
    {
        return [TestActionType::CORRECT_ANSWER, TestActionType::SHOW_ANSWER];
    }

    public function getFinishedTypesIds(): array
    {
        $finishedActionTypes = $this->findBy([
            'name' => self::getFinishedTypesName()
        ]);

        return array_map(
            fn (TestActionType $actionType) => $actionType->getId(),
            $finishedActionTypes
        );
    }
}
