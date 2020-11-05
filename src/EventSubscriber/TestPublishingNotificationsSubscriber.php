<?php

namespace App\EventSubscriber;

use App\Enum\TestTransition;
use App\Entity\Test;
use App\Service\FrontendLinkService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestPublishingNotificationsSubscriber implements EventSubscriberInterface
{
    private MailerInterface $mailer;
    private FrontendLinkService $frontendLinkService;
    private TranslatorInterface $translator;

    public function __construct(
        MailerInterface $mailer,
        FrontendLinkService $frontendLinkService,
        TranslatorInterface $translator
    ) {
        $this->mailer = $mailer;
        $this->frontendLinkService = $frontendLinkService;
        $this->translator = $translator;
    }

    public function onReviewedTest(Event $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        if (!$test->getModerator()) {
            throw new \LogicException('Moderator is not defined.');
        }

        $testUrl = $this->frontendLinkService->getModerationTestUrl($test->getId());

        $email = (new Email())
            ->to($test->getModerator()->getEmail())
            ->subject($this->translator->trans('TEST_MODERATOR_VERIFICATION_EMAIL.TITLE'))
            ->text($this->translator->trans('TEST_MODERATOR_VERIFICATION_EMAIL.BODY', ['#link#' => $testUrl]));

        $this->mailer->send($email);
    }

    public function onRejectedTest(Event $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        $testUrl = $this->frontendLinkService->getAccountTestUrl($test->getId());

        $email = (new Email())
            ->to($test->getCreatedBy()->getEmail())
            ->subject($this->translator->trans('TEST_ON_CORRECTION_EMAIL.TITLE'))
            ->text($this->translator->trans('TEST_ON_CORRECTION_EMAIL.BODY', ['#link#' => $testUrl]));

        $this->mailer->send($email);
    }

    public function onPublishTest(Event $event)
    {
        /** @var Test $test */
        $test = $event->getSubject();

        $test->setPublishedAt(new \DateTime());

        $testUrl = $this->frontendLinkService->getAccountTestUrl($test->getId());

        $email = (new Email())
            ->to($test->getCreatedBy()->getEmail())
            ->subject($this->translator->trans('TEST_IS_PUBLISHED_EMAIL.TITLE'))
            ->text($this->translator->trans('TEST_IS_PUBLISHED_EMAIL.BODY', ['#link#' => $testUrl]));

        $this->mailer->send($email);
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.test_publishing.completed.' . TestTransition::TO_REVIEW => 'onReviewedTest',
            'workflow.test_publishing.completed.' . TestTransition::REJECT => 'onRejectedTest',
            'workflow.test_publishing.completed.' . TestTransition::PUBLISH => 'onPublishTest',
        ];
    }
}
