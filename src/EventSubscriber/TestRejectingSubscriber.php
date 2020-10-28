<?php

namespace App\EventSubscriber;

use App\Entity\ChatMessage;
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

    public function guardReject(GuardEvent $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        $chatMessageRepository = $this->em->getRepository(ChatMessage::class);
        /** @var ChatMessage $lastModeratorMessage */
        $lastModeratorMessage = $chatMessageRepository->getLastModeratorMessage($test);

        if (!$lastModeratorMessage ||
            $lastModeratorMessage->getCreatedAt() < $test->getUpdatedAt()
        ) {
            $blocker = new TransitionBlocker('Empty message', 'EMPTY_MESSAGE_ON_MODERATOR_REJECT');
            $event->addTransitionBlocker($blocker);
        }
    }
    
    public static function getSubscribedEvents()
    {
        return [
            'workflow.test_publishing.guard.' . TestTransition::REJECT => 'guardReject',
        ];
    }
}
