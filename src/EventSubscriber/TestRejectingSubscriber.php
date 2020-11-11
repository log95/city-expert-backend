<?php

namespace App\EventSubscriber;

use App\Entity\TestComment;
use App\Enum\TestTransition;
use App\Entity\Test;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class TestRejectingSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function denyRejectWithoutComment(GuardEvent $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        $commentsRepository = $this->em->getRepository(TestComment::class);
        /** @var TestComment $lastModeratorComment */
        $lastModeratorComment = $commentsRepository->getLastModeratorMessage($test);

        if (!$lastModeratorComment ||
            $lastModeratorComment->getCreatedAt() < $test->getUpdatedAt()
        ) {
            $blocker = new TransitionBlocker('No comment', 'NO_COMMENT_ON_MODERATOR_REJECT');
            $event->addTransitionBlocker($blocker);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.test_publishing.guard.' . TestTransition::REJECT => 'denyRejectWithoutComment',
        ];
    }
}
