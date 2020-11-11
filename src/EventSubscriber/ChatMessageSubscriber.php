<?php

namespace App\EventSubscriber;

use App\Entity\TestComment;
use App\Service\FrontendLinkService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChatMessageSubscriber implements EventSubscriber
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

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (!($args->getObject() instanceof TestComment)) {
            return;
        }

        /** @var TestComment $comment */
        $comment = $args->getObject();
        $commentCreatorId = $comment->getCreatedBy()->getId();

        $test = $comment->getTest();

        $isCommentByTestCreator = $commentCreatorId === $test->getCreatedBy()->getId();

        $emailToUser = $isCommentByTestCreator ?
            $test->getModerator() :
            $test->getCreatedBy();

        $testUrl = $isCommentByTestCreator ?
            $this->frontendLinkService->getModerationTestUrl($test->getId()) :
            $this->frontendLinkService->getAccountTestUrl($test->getId());

        $email = (new Email())
            ->to($emailToUser->getEmail())
            ->subject($this->translator->trans('NEW_TEST_COMMENT_EMAIL.TITLE'))
            ->text($this->translator->trans('NEW_TEST_COMMENT_EMAIL.BODY', ['#link#' => $testUrl]));

        $this->mailer->send($email);
    }
}
