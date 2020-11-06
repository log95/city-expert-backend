<?php

namespace App\EventSubscriber;

use App\Enum\TestTransition;
use App\Entity\Test;
use App\Service\ModeratorService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class TestToReviewSubscriber implements EventSubscriberInterface
{
    private ModeratorService $moderatorService;

    public function __construct(ModeratorService $moderatorService)
    {
        $this->moderatorService = $moderatorService;
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
            'workflow.test_publishing.transition.' . TestTransition::TO_REVIEW => 'setModerator',
        ];
    }
}
