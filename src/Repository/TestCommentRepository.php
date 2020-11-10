<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TestComment;
use App\Entity\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TestComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestComment[]    findAll()
 * @method TestComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestComment::class);
    }

    public function getMessages(Test $test): array
    {
        $comments = $this->createQueryBuilder('test_comment')
            ->select([
                'test_comment.id as id',
                'test_comment.message as message',
                'creator.id as author_id',
                'creator.name as author_name',
            ])
            ->leftJoin('test_comment.createdBy', 'creator')
            ->andWhere('test_comment.test = :test')
            ->setParameters([
                'test' => $test,
            ])
            ->orderBy('test_comment.createdAt', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $testCreatorId = $test->getCreatedBy()->getId();

        return array_map(function ($comment) use ($testCreatorId) {
            $comment['by_test_creator'] = $testCreatorId === $comment['author_id'];

            return $comment;
        }, $comments);
    }

    public function getLastModeratorMessage(Test $test): ?TestComment
    {
        return $this->createQueryBuilder('test_comment')
            ->andWhere('test_comment.test = :test')
            ->andWhere('test_comment.createdBy != :testCreator')
            ->setParameters([
                'test' => $test,
                'testCreator' => $test->getCreatedBy(),
            ])
            ->orderBy('test_comment.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
