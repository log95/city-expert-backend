<?php

namespace App\EventSubscriber;

use App\Enum\TestTransition;
use App\Entity\Test;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class TestToCorrectionSubscriber implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function checkUserIsCreator(GuardEvent $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        if ($test->getCreatedBy()->getId() !== $this->security->getUser()->getId()) {
            $blocker = new TransitionBlocker('Need to be creator', 'NOT_CREATOR_TEST_TO_CORRECTION');
            $event->addTransitionBlocker($blocker);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.test_publishing.guard.' . TestTransition::BACK_TO_CORRECTION => 'checkUserIsCreator',
        ];
    }
}
