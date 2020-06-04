<?php

namespace App\EventSubscriber;

use App\Entity\Chat;
use App\Entity\Test;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class TestCreateSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (!($args->getObject() instanceof Test)) {
            return;
        }

        /** @var Test $test */
        $test = $args->getObject();

        /** @var EntityManagerInterface $em */
        $em = $args->getEntityManager();

        $chat = new Chat($test);

        $em->persist($chat);
        $em->flush();
    }
}