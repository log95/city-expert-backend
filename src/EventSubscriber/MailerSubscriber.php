<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

class MailerSubscriber implements EventSubscriberInterface
{
    private string $sender;

    public function __construct(string $sender)
    {
        $this->sender = $sender;
    }

    public function setSender(MessageEvent $event)
    {
        $email = $event->getMessage();
        if (!$email instanceof Email) {
            return;
        }

        if ($email->getFrom()) {
            return;
        }

        $email->from($this->sender);
    }

    public static function getSubscribedEvents()
    {
        return [
            MessageEvent::class => 'setSender',
        ];
    }
}
