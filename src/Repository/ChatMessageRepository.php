<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\Test;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatMessage[]    findAll()
 * @method ChatMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    public function getMessages(Chat $chat)
    {
        // TODO: сделать пагинацию
        $query = $this->createQueryBuilder('message')
            ->andWhere('message.chat = :chat')
            ->setParameter('chat', $chat)
            ->orderBy('message.id', 'DESC')
            //->setFirstResult(0)
            //->setMaxResults(10)
            ->getQuery();

        $paginator = new Paginator($query, false);

        $testCreatorId = $chat->getTest()->getCreatedBy()->getId();

        $messages = [];
        /** @var ChatMessage $message */
        foreach ($paginator as $message) {
            $messages[] = [
                'id' => $message->getId(),
                'text' => $message->getMessage(),
                'by_creator' => $testCreatorId === $message->getCreatedBy()->getId(),
            ];
        }

        return [
            'count' => count($paginator),
            'messages' => $messages,
        ];
    }

    public function getLastModeratorMessage(Test $test): ?ChatMessage
    {
        return $this->createQueryBuilder('message')
            ->andWhere('message.chat = :chat')
            ->andWhere('message.createdBy != :testCreator')
            ->setParameters([
                'chat' => $test->getChat(),
                'testCreator' => $test->getCreatedBy(),
            ])
            ->orderBy('message.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
