<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AuthOperation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuthOperation|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthOperation|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthOperation[]    findAll()
 * @method AuthOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthOperation::class);
    }

    public function isValidCode(User $user, string $type, string $code, int $ttl): bool
    {
        $limitTime = (new \DateTime())->modify('- ' . $ttl . ' seconds');

        $operation = $this->createQueryBuilder('auth_operation')
            ->andWhere('auth_operation.user = :user')
            ->andWhere('auth_operation.type = :type')
            ->andWhere('auth_operation.code = :code')
            ->andWhere('auth_operation.createdAt > :limitTime')
            ->setParameters([
                'user' => $user,
                'type' => $type,
                'code' => $code,
                'limitTime' => $limitTime,
            ])
            ->getQuery()
            ->getOneOrNullResult();

        return !\is_null($operation);
    }
}
