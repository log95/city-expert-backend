<?php

namespace App\EventSubscriber;

use App\Entity\TestComment;
use App\Service\FrontendLinkService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ChatMessageSubscriber implements EventSubscriber
{
    private MailerInterface $mailer;
    private FrontendLinkService $frontendLinkService;

    public function __construct(
        MailerInterface $mailer,
        FrontendLinkService $frontendLinkService
    ) {
        $this->mailer = $mailer;
        $this->frontendLinkService = $frontendLinkService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        // TODO: тесты  и переделать.
        if (!($args->getObject() instanceof TestComment)) {
            return;
        }

        /** @var TestComment $message */
        $message = $args->getObject();
        $messageCreatorId = $message->getCreatedBy()->getId();

        $test = $message->getTest();

        $isMessageByTestCreator = $messageCreatorId === $test->getCreatedBy()->getId();

        $emailToUser = $isMessageByTestCreator ?
            $test->getModerator() :
            $test->getCreatedBy();

        $testLink = $isMessageByTestCreator ?
            $this->frontendLinkService->getModerationTestUrl($test->getId()) :
            $this->frontendLinkService->getAccountTestUrl($test->getId());

        $email = (new Email())
            ->to($emailToUser->getEmail())
            ->subject('New comment on test.')
            ->text('New comment on test: ' . $testLink);

        $this->mailer->send($email);
    }
}
