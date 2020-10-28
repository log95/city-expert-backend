<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Test;
use App\Entity\TestInterest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestInterest|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestInterest|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestInterest[]    findAll()
 * @method TestInterest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestInterestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestInterest::class);
    }

    public function createOrUpdate(User $user, Test $test, bool $isLiked): void
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $sql = '
                INSERT INTO test_interest(id, user_id, test_id, is_liked)
                VALUES(:id, :user_id, :test_id, :is_liked)
                ON CONFLICT (user_id, test_id) DO UPDATE
                SET is_liked = :is_liked
        ';
        $conn->executeStatement($sql, [
            'id' => $em->getClassMetadata(TestInterest::class)->idGenerator->generate($em, null),
            'user_id' => $user->getId(),
            'test_id' => $test->getId(),
            'is_liked' => $isLiked,
        ], [
            'is_liked' => ParameterType::BOOLEAN,
        ]);
    }

    public function getCounts(Test $test): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $qb = $conn->createQueryBuilder();

        $stmt = $qb
            ->select([
                'COUNT(is_liked) filter (where is_liked = true) as likes',
                'COUNT(is_liked) filter (where is_liked = false) as dislikes',
            ])
            ->from('test_interest')
            ->andWhere('test_id = :test_id')
            ->setParameter('test_id', $test->getId())
            ->execute();

        return $stmt->fetchAssociative();
    }

    public function isUserLiked(User $user, Test $test): ?bool
    {
        $interestInfo = $this->createQueryBuilder('interest')
            ->select('interest.isLiked')
            ->andWhere('interest.user = :user')
            ->andWhere('interest.test = :test')
            ->setParameters([
                'user' => $user,
                'test' => $test,
            ])
            ->getQuery()
            ->getOneOrNullResult();

        return $interestInfo ? $interestInfo['isLiked'] : null;
    }
}
