<?php

namespace App\EventSubscriber;

use App\Entity\Enum\TestTransition;
use App\Entity\Test;
use App\Service\ModeratorService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Workflow\Event\Event;

class TestPublishingSubscriber implements EventSubscriberInterface
{
    private ModeratorService $moderatorService;
    private MailerInterface $mailer;

    public function __construct(ModeratorService $moderatorService, MailerInterface $mailer)
    {
        $this->moderatorService = $moderatorService;
        $this->mailer = $mailer;
    }

    public function setModerator(Event $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        if ($test->getModerator()) {
            return;
        }

        $moderator = $this->moderatorService->determineModeratorForTest($test);

        $test->setModerator($moderator);

        $email = (new Email())
            ->from('test@yandex.ru') // TODO: надо вынести
            ->to($moderator->getEmail())
            ->subject('New test for check.')
            ->text('Test for check: link'); // TODO: ссылка для модератора (в env path уже добавил)

        $this->mailer->send($email);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.test_publishing.transition.' . TestTransition::TO_REVIEW => 'setModerator',
        ];
    }
}
