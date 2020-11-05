<?php

namespace App\EventSubscriber;

use App\Enum\TestTransition;
use App\Entity\Test;
use App\Service\ModeratorService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class TestToReviewSubscriber implements EventSubscriberInterface
{
    private ModeratorService $moderatorService;
    private Security $security;

    public function __construct(ModeratorService $moderatorService, Security $security)
    {
        $this->moderatorService = $moderatorService;
        $this->security = $security;
    }

    public function checkUserIsCreator(GuardEvent $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        if ($test->getCreatedBy()->getId() !== $this->security->getUser()->getId()) {
            $blocker = new TransitionBlocker('Need to be creator', 'NOT_CREATOR_TEST_TO_REVIEW');
            $event->addTransitionBlocker($blocker);
        }
    }

    public function setModerator(Event $event): void
    {
        /** @var Test $test */
        $test = $event->getSubject();

        if ($test->getModerator()) {
            return;
        }

        $moderator = $this->moderatorService->determineModeratorForTest();

        $test->setModerator($moderator);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.test_publishing.guard.' . TestTransition::TO_REVIEW => 'checkUserIsCreator',
            'workflow.test_publishing.transition.' . TestTransition::TO_REVIEW => 'setModerator',
        ];
    }
}
