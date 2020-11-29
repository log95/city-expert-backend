<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\TestComment;
use App\Enum\WsMessageType;
use App\Service\FrontendLinkService;
use App\Service\WebSocket\WsService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestCommentSubscriber implements EventSubscriber
{
    private MailerInterface $mailer;
    private FrontendLinkService $frontendLinkService;
    private TranslatorInterface $translator;
    private WsService $wsService;

    public function __construct(
        MailerInterface $mailer,
        FrontendLinkService $frontendLinkService,
        TranslatorInterface $translator,
        WsService $wsService
    ) {
        $this->mailer = $mailer;
        $this->frontendLinkService = $frontendLinkService;
        $this->translator = $translator;
        $this->wsService = $wsService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        if (!($args->getObject() instanceof TestComment)) {
            return;
        }

        /** @var TestComment $comment */
        $comment = $args->getObject();
        $commentCreatorId = $comment->getCreatedBy()->getId();

        $test = $comment->getTest();

        $isCommentByTestCreator = $commentCreatorId === $test->getCreatedBy()->getId();

        $userToNotify = $isCommentByTestCreator ?
            $test->getModerator() :
            $test->getCreatedBy();

        $testUrl = $isCommentByTestCreator ?
            $this->frontendLinkService->getModerationTestUrl($test->getId()) :
            $this->frontendLinkService->getAccountTestUrl($test->getId());

        $isMessageSended = $this->wsService->sendMessage($userToNotify, [
            'TYPE' => WsMessageType::NEW_TEST_COMMENT,
            'TEST_ID' => $test->getId(),
            'TEST_URL' => $testUrl,
            'TEST_QUESTION' => $test->getQuestion(),
        ]);

        if ($isMessageSended) {
            return;
        }

        $email = (new Email())
            ->to($userToNotify->getEmail())
            ->subject($this->translator->trans('NEW_TEST_COMMENT_EMAIL.TITLE'))
            ->text($this->translator->trans('NEW_TEST_COMMENT_EMAIL.BODY', ['#link#' => $testUrl]));

        $this->mailer->send($email);
    }
}
