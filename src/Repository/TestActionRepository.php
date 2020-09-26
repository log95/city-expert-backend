<?php

namespace App\Repository;

use App\Entity\TestStatus;
use App\Entity\Test;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use App\Entity\TestHint;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestAction[]    findAll()
 * @method TestAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestAction::class);
    }

    public function getTestListForUser(User $user, int $page, int $perPage, string $sortBy, string $sortDirection, array $filterBy): array
    {
        $conn = $this->getEntityManager()->getConnection();

        if (!$filterBy['city_id']) {
            throw new \RuntimeException('City is required.');
        }

        $testActionTypeRepository = $this->getEntityManager()->getRepository(TestActionType::class);
        $finishedActionTypes = $testActionTypeRepository->findBy(['name' => TestActionTypeRepository::getFinishedTypesName()]);
        $finishedActionTypesIds = array_map(fn (TestActionType $actionType) => $actionType->getId(), $finishedActionTypes);

        $sortBy = $sortBy ?: 'published_at';
        $sortDirection = $sortDirection ?? 'DESC';

        //$orderBy = 'test.id';
        //$orderBy = 'likes';
        //$direction = 'ASC';

        //$perPage = 10;
        $offset = ($page - 1)  * $perPage;

        $qb = $conn->createQueryBuilder();
        $qb
            ->select([
                'test.id',
                'test.image_url',
                'MIN(test_action_type.name) as action_type_name',
                'COUNT(test_interest.is_liked) filter (where test_interest.is_liked = true) as likes',
                'COUNT(test_interest.is_liked) filter (where test_interest.is_liked = false) as dislikes',
            ])
            ->from('test', 'test')
            ->leftJoin('test', 'test_action', 'test_action', $qb->expr()->andX(
                $qb->expr()->eq('test.id', 'test_action.test_id'),
                $qb->expr()->eq('test_action.user_id', ':user_id'),
                $qb->expr()->in('test_action.type_id', ':action_type_ids')
            ))
            ->leftJoin('test_action', 'test_action_type', 'test_action_type', 'test_action.type_id = test_action_type.id')
            ->leftJoin('test', 'test_interest', 'test_interest', 'test.id = test_interest.test_id')
            ->andWhere('test.published_at IS NOT NULL')
            ->andWhere('test.city_id = :city_id')
            ->groupBy('test.id')
            ->orderBy($sortBy, $sortDirection)
            ->setMaxResults($perPage)
            ->setFirstResult($offset)
            ->setParameters([
                'user_id' => $user->getId(),
                'city_id' => $filterBy['city_id'],
                'action_type_ids' => $finishedActionTypesIds,
            ], [
                'action_type_ids' => Connection::PARAM_INT_ARRAY,
            ]);

        if ($filterBy['status']) {
            switch ($filterBy['status'])
            {
                case TestStatus::CORRECT_ANSWER:
                    $qb->andWhere('test_action_type.name = :action_name')->setParameter('action_name', TestActionType::CORRECT_ANSWER);
                    break;

                case TestStatus::SHOW_ANSWER:
                    $qb->andWhere('test_action_type.name = :action_name')->setParameter('action_name', TestActionType::SHOW_ANSWER);
                    break;

                case TestStatus::IN_PROCESS:
                    $qb->andWhere('test_action_type.name IS NULL');
                    break;

                default:
                    throw new \RuntimeException('');
            }
        }

        $stmt = $qb->execute();
        $tests = $stmt->fetchAll();

        $data = array_map(function (array $test) {
            if (isset($test['action_type_name'])) {
                $status = $test['action_type_name'] === TestActionType::SHOW_ANSWER ?
                    TestStatus::SHOW_ANSWER :
                    TestStatus::CORRECT_ANSWER;
            } else {
                $status = TestStatus::IN_PROCESS;
            }

            return [
                'id' => $test['id'],
                'image_url' => $test['image_url'],
                'likes' => $test['likes'],
                'dislikes' => $test['dislikes'],
                'status' => $status,
            ];
        }, $tests);

        $countInfo = $qb->select('COUNT(*) OVER ()')
            ->resetQueryPart('orderBy')
            ->setMaxResults(1)
            ->execute()
            ->fetch();

        return [
            'tests' => $data,
            'count' => $countInfo['count'],
        ];
    }

    // TODO: remove?
    private function getStatusFinishedTests(User $user): array
    {
        $tests = $this->createQueryBuilder('test_action')
            ->select(['IDENTITY(test_action.test) as test_id', 'action_type.name as action_type_name'])
            ->leftJoin('test_action.type', 'action_type')
            ->andWhere('test_action.user = :user')
            ->andWhere('action_type.name IN (:action_type_names)')
            ->setParameters([
                'user' => $user,
                'action_type_names' => [TestActionType::CORRECT_ANSWER, TestActionType::SHOW_ANSWER],
            ])
            ->getQuery()
            ->getResult();

        $result = [];

        foreach ($tests as $testInfo) {
            $result[$testInfo['test_id']] = $testInfo['action_type_name'] === TestActionType::CORRECT_ANSWER ?
                TestStatus::CORRECT_ANSWER :
                TestStatus::SHOW_ANSWER;
        }

        return $result;
    }

    public function getStatus(User $user, Test $test): string
    {
        $test = $this->createQueryBuilder('test_action')
            ->select(['action_type.name as action_type_name'])
            ->leftJoin('test_action.type', 'action_type')
            ->andWhere('test_action.user = :user')
            ->andWhere('test_action.test = :test')
            ->andWhere('action_type.name IN (:action_type_names)')
            ->setParameters([
                'user' => $user,
                'test' => $test,
                'action_type_names' => [TestActionType::CORRECT_ANSWER, TestActionType::SHOW_ANSWER],
            ])
            ->getQuery()
            ->getOneOrNullResult();

        if (!$test) {
            return TestStatus::IN_PROCESS;
        }

        return $test['action_type_name'] ===  TestActionType::CORRECT_ANSWER ?
            TestStatus::CORRECT_ANSWER :
            TestStatus::SHOW_ANSWER;
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
