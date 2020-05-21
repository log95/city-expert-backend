<?php

namespace App\Repository;

use App\Entity\Enum\TestStatus;
use App\Entity\Test;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use App\Entity\TestHint;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function getAllTestsWithStatus(User $user): array
    {
        $testRepository = $this->getEntityManager()->getRepository(Test::class);

        $tests = $testRepository->createQueryBuilder('test')
            ->select(['test.id', 'test.imageUrl as image_url'])
            ->getQuery()
            ->getResult();

        $finishedTests = $this->getStatusFinishedTests($user);

        foreach ($tests as $key => $test) {
            $tests[$key]['status'] = $finishedTests[$test['id']] ?? TestStatus::IN_PROCESS;
        }

        return $tests;
    }

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
