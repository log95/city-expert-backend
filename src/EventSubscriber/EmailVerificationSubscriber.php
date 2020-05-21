<?php

namespace App\EventSubscriber;

use App\Entity\Points;
use App\Entity\PointsType;
use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
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

        /** @var EntityManagerInterface $em */
        $em = $args->getEntityManager();

        $pointsTypeRepository = $em->getRepository(PointsType::class);
        /** @var PointsType $pointsType */
        $pointsType = $pointsTypeRepository->findOneBy(['name' => PointsType::REGISTRATION]);

        $pointsEntity = new Points();
        $pointsEntity->setUser($user);
        $pointsEntity->setType($pointsType);
        $pointsEntity->setPoints($pointsType->getPoints());

        $em->persist($pointsEntity);
        $em->flush();
    }
}
