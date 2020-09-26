<?php

namespace App\EventSubscriber;

use App\Entity\TestTransition;
use App\Entity\Test;
use App\Service\FrontendLinkService;
use App\Service\ModeratorService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Workflow\Event\Event;

class TestPublishingSubscriber implements EventSubscriberInterface
{
    private ModeratorService $moderatorService;
    private MailerInterface $mailer;
    private FrontendLinkService $frontendLinkService;

    public function __construct(
        ModeratorService $moderatorService,
        MailerInterface $mailer,
        FrontendLinkService $frontendLinkService
    ) {
        $this->moderatorService = $moderatorService;
        $this->mailer = $mailer;
        $this->frontendLinkService = $frontendLinkService;
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
    }

    public function onReviewedTest(Event $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        if (!$test->getModerator()) {
            throw new \LogicException('Moderator is not defined.');
        }

        $email = (new Email())
            ->to($test->getModerator()->getEmail())
            ->subject('Test verification.')
            ->text('Test for check: ' . $this->frontendLinkService->getModerationTestUrl($test->getId()));

        $this->mailer->send($email);
    }

    public function onRejectedTest(Event $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        $email = (new Email())
            ->to($test->getCreatedBy()->getEmail())
            ->subject('Test correction.')
            ->text('Moderator leaves new info. Test: ' . $this->frontendLinkService->getAccountTestUrl($test->getId()));

        $this->mailer->send($email);
    }

    public function onApprovedTest(Event $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        $test->setPublishedAt(new \DateTime());

        $email = (new Email())
            ->to($test->getCreatedBy()->getEmail())
            ->subject('Test is approved.')
            ->text('Test is approved. Test: ' . $this->frontendLinkService->getAccountTestUrl($test->getId()));

        $this->mailer->send($email);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.test_publishing.transition.' . TestTransition::TO_REVIEW => 'setModerator',
            'workflow.test_publishing.completed.' . TestTransition::TO_REVIEW => 'onReviewedTest',
            'workflow.test_publishing.completed.' . TestTransition::REJECT => 'onRejectedTest',
            'workflow.test_publishing.completed.' . TestTransition::APPROVE => 'onApprovedTest',
        ];
    }
}
