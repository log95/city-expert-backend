<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JwtCreatedSubscriber implements EventSubscriberInterface
{
    public function addUserIdToJwt(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        $payload = $event->getData();
        $payload['id'] = $user->getId();

        $event->setData($payload);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::JWT_CREATED => 'addUserIdToJwt',
        ];
    }
}
