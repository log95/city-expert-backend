<?php

declare(strict_types=1);

namespace App\Repository;

use App\Enum\TestPublishStatus;
use App\Enum\TestStatus;
use App\Entity\Test;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use App\Entity\TestHint;
use App\Entity\User;
use App\Exceptions\TestNotPublishedException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use App\Exceptions\FilterException;

/**
 * @method TestAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestAction[]    findAll()
 * @method TestAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestActionRepository extends ServiceEntityRepository
{
    public const DEFAULT_PER_PAGE = 10;
    public const MAX_PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestAction::class);
    }

    public function getTestListForUser(
        User $user,
        ?int $page,
        ?int $perPage,
        ?string $sortBy,
        ?string $sortDirection,
        ?array $filterBy
    ): array {
        $page = $page ?: 1;
        $perPage = $perPage ?: self::DEFAULT_PER_PAGE;
        $sortBy = $sortBy ?: 'published_at';
        $sortDirection = $sortDirection ?: 'DESC';
        $filterBy = $filterBy ?: [];

        if ($perPage > self::MAX_PER_PAGE) {
            throw new FilterException('PER_PAGE_LIMIT_EXCEEDS');
        }

        $conn = $this->getEntityManager()->getConnection();

        $testActionTypeRepository = $this->getEntityManager()->getRepository(TestActionType::class);
        $finishedActionTypesIds = $testActionTypeRepository->getFinishedTypesIds();

        $offset = ($page - 1)  * $perPage;

        $qb = $conn->createQueryBuilder();
        $qb
            ->select([
                'test.id',
                'test.image_url',
                'MIN(test_action_type.name) as action_type_name',   // Need to apply aggregate func because of grouping.
                'COUNT(test_interest.is_liked) filter (where test_interest.is_liked = true) as likes',
                'COUNT(test_interest.is_liked) filter (where test_interest.is_liked = false) as dislikes',
            ])
            ->from('test', 'test')
            ->leftJoin('test', 'test_action', 'test_action', $qb->expr()->and(
                $qb->expr()->eq('test.id', 'test_action.test_id'),
                $qb->expr()->eq('test_action.user_id', ':user_id'),
                $qb->expr()->in('test_action.type_id', ':action_type_ids')
            ))
            ->leftJoin(
                'test_action',
                'test_action_type',
                'test_action_type',
                'test_action.type_id = test_action_type.id'
            )
            ->leftJoin(
                'test',
                'test_interest',
                'test_interest',
                'test.id = test_interest.test_id'
            )
            ->andWhere('test.current_status = :test_status')
            ->groupBy('test.id')   // Group for likes counting.
            ->orderBy($sortBy, $sortDirection)
            ->setMaxResults($perPage)
            ->setFirstResult($offset)
            ->setParameters([
                'user_id' => $user->getId(),
                'action_type_ids' => $finishedActionTypesIds,
                'test_status' => TestPublishStatus::PUBLISHED,
            ], [
                'action_type_ids' => Connection::PARAM_INT_ARRAY,
            ]);

        if (!empty($filterBy['city_id'])) {
            $qb->andWhere('test.city_id = :city_id')->setParameter('city_id', $filterBy['city_id']);
        }

        if (!empty($filterBy['status'])) {
            switch ($filterBy['status']) {
                case TestStatus::CORRECT_ANSWER:
                    $qb->andWhere('test_action_type.name = :action_name')
                        ->setParameter('action_name', TestActionType::CORRECT_ANSWER);
                    break;

                case TestStatus::SHOW_ANSWER:
                    $qb->andWhere('test_action_type.name = :action_name')
                        ->setParameter('action_name', TestActionType::SHOW_ANSWER);
                    break;

                case TestStatus::IN_PROCESS:
                    // If no test action for user, it means no finished actions.
                    $qb->andWhere('test_action_type.name IS NULL');
                    break;

                default:
                    throw new FilterException('UNKNOWN_FILTER_STATUS_PARAM');
            }
        }

        $stmt = $qb->execute();
        $tests = $stmt->fetchAllAssociative();

        $data = array_map(function (array $test) {
            return [
                'id' => $test['id'],
                'image_url' => $test['image_url'],
                'likes' => $test['likes'],
                'dislikes' => $test['dislikes'],
                'status' => $this->getTestStatusByActionType($test['action_type_name']),
            ];
        }, $tests);

        $count = $qb->select('COUNT(*) OVER ()')
            ->resetQueryPart('orderBy')
            ->setMaxResults(1)
            ->execute()
            ->fetchOne();

        return [
            'tests' => $data,
            'count' => $count,
        ];
    }

    /**
     * Get status of test (published) for user.
     * @param User $user
     * @param Test $test
     * @return string
     */
    public function getTestStatus(User $user, Test $test): string
    {
        if (!$test->isPublished()) {
            throw new TestNotPublishedException('Test status is available only on published tests.');
        }

        $testInfo = $this->createQueryBuilder('test_action')
            ->select(['action_type.name as action_type_name'])
            ->leftJoin('test_action.type', 'action_type')
            ->andWhere('test_action.user = :user')
            ->andWhere('test_action.test = :test')
            ->andWhere('action_type.name IN (:action_type_names)')
            ->setParameters([
                'user' => $user,
                'test' => $test,
                'action_type_names' => TestActionTypeRepository::getFinishedTypesName(),
            ])
            ->getQuery()
            ->getOneOrNullResult();

        if (!$testInfo) {
            return TestStatus::IN_PROCESS;
        }

        return $this->getTestStatusByActionType($testInfo['action_type_name']);
    }

    public function getTestStatusByActionType(?string $actionTypeName): string
    {
        switch ($actionTypeName) {
            case TestActionType::CORRECT_ANSWER:
                return TestStatus::CORRECT_ANSWER;

            case TestActionType::SHOW_ANSWER:
                return TestStatus::SHOW_ANSWER;

            default:
                return TestStatus::IN_PROCESS;
        }
    }

    public function getUsedHintIds(User $user, Test $test): array
    {
        $hints = $this->createQueryBuilder('test_action')
            ->select('IDENTITY(test_action.hint) as id')
            ->leftJoin('test_action.type', 'action_type')
            ->andWhere('test_action.user = :user')
            ->andWhere('test_action.test = :test')
            ->andWhere('action_type.name = :action_type_name')
            ->setParameters([
                'user' => $user,
                'test' => $test,
                'action_type_name' => TestActionType::SHOW_HINT,
            ])
            ->getQuery()
            ->getArrayResult();

        return array_map(fn ($hint) => $hint['id'], $hints);
    }

    public function isUsedHint(User $user, Test $test, TestHint $hint): bool
    {
        $hint = $this->createQueryBuilder('test_action')
            ->select('IDENTITY(test_action.hint) as id')
            ->leftJoin('test_action.type', 'action_type')
            ->andWhere('test_action.user = :user')
            ->andWhere('test_action.test = :test')
            ->andWhere('test_action.hint = :hint')
            ->andWhere('action_type.name = :action_type_name')
            ->setParameters([
                'user' => $user,
                'test' => $test,
                'hint' => $hint,
                'action_type_name' => TestActionType::SHOW_HINT,
            ])
            ->getQuery()
            ->getOneOrNullResult();

        return !\is_null($hint);
    }
}
