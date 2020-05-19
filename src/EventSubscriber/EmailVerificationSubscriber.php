<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\PointsService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class EmailVerificationSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (!($args->getObject() instanceof User)) {
            return;
        }

        /** @var User $user */
        $user = $args->getObject();

        $pointsService = new PointsService($args->getEntityManager());
        $pointsService->addForRegistration($user);
    }
}
