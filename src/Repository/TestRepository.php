<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\TestPublishStatus;
use App\Entity\Test;
use App\Entity\User;
use App\Exceptions\FilterException;
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
    public const DEFAULT_PER_PAGE = 10;
    public const MAX_PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Test::class);
    }

    /**
     * Get tests created by user.
     *
     * @param User $user
     * @param int|null $page
     * @param int|null $perPage
     * @param string|null $sortBy
     * @param string|null $sortDirection
     * @param array|null $filterBy
     * @return array
     */
    public function getCreatedTests(
        User $user,
        ?int $page,
        ?int $perPage,
        ?string $sortBy,
        ?string $sortDirection,
        ?array $filterBy
    ): array {
        $page = $page ?: 1;
        $perPage = $perPage ?: self::DEFAULT_PER_PAGE;
        $sortBy = $sortBy ?: 'updated_at';
        $sortDirection = $sortDirection ?: 'DESC';
        $filterBy = $filterBy ?: [];

        if ($perPage > self::MAX_PER_PAGE) {
            throw new FilterException('PER_PAGE_LIMIT_EXCEEDS');
        }

        if (!in_array($sortBy, ['created_at', 'updated_at'])) {
            throw new FilterException('SORT_BY_PARAM_NOT_ALLOWED');
        }

        if (!in_array($sortDirection, ['ASC', 'DESC'])) {
            throw new FilterException('SORT_DIRECTION_PARAM_NOT_ALLOWED');
        }

        $offset = ($page - 1)  * $perPage;

        $conn = $this->getEntityManager()->getConnection();

        $qb = $conn->createQueryBuilder()
            ->select([
                'test.id',
                'test.image_url',
            ])
            ->from('test', 'test')
            ->where('test.created_by_id = :user_id')
            ->orderBy($sortBy, $sortDirection)
            ->setMaxResults($perPage)
            ->setFirstResult($offset)
            ->setParameter('user_id', $user->getId());

        if (!empty($filterBy['city_id'])) {
            $qb->andWhere('test.city_id = :city_id')->setParameter('city_id', $filterBy['city_id']);
        }

        if (!empty($filterBy['status'])) {
            switch ($filterBy['status']) {
                case TestPublishStatus::REVIEWED:
                case TestPublishStatus::ON_CORRECTION:
                case TestPublishStatus::PUBLISHED:
                    $qb->andWhere('test.current_status = :test_status')
                        ->setParameter('test_status', $filterBy['status']);
                    break;

                default:
                    throw new FilterException('UNKNOWN_FILTER_STATUS_PARAM');
            }
        }

        $tests = $qb->execute()->fetchAllAssociative();

        $count = $qb->select('COUNT(*) OVER ()')
            ->setMaxResults(1)
            ->execute()
            ->fetchOne();

        return [
            'tests' => $tests,
            'count' => $count,
        ];
    }

    public function getNearPublishedTests(Test $test): array
    {
        $prevTest = $this->createQueryBuilder('test')
            ->select('test.id')
            ->andWhere('IDENTITY(test.city) = :city_id')
            ->andWhere('test.id < :test_id')
            ->andWhere('test.currentStatus = :test_publish_status')
            ->setParameters([
                'city_id' => $test->getCity()->getId(),
                'test_id' => $test->getId(),
                'test_publish_status' => TestPublishStatus::PUBLISHED,
            ])
            ->orderBy('test.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $nextTest = $this->createQueryBuilder('test')
            ->select('test.id')
            ->andWhere('IDENTITY(test.city) = :city_id')
            ->andWhere('test.id > :test_id')
            ->andWhere('test.currentStatus = :test_publish_status')
            ->setParameters([
                'city_id' => $test->getCity()->getId(),
                'test_id' => $test->getId(),
                'test_publish_status' => TestPublishStatus::PUBLISHED,
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
