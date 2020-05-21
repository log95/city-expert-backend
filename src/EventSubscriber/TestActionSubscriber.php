<?php

namespace App\EventSubscriber;

use App\Entity\Points;
use App\Entity\PointsType;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class TestActionSubscriber implements EventSubscriber
{
    const ACTIONS_TO_POINTS_TYPE_MAP = [
        TestActionType::CORRECT_ANSWER => PointsType::CORRECT_ANSWER,
        TestActionType::SHOW_ANSWER => PointsType::SHOW_ANSWER,
        TestActionType::SHOW_HINT => PointsType::SHOW_HINT,
    ];

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (!($args->getObject() instanceof TestAction)) {
            return;
        }

        /** @var TestAction $testAction */
        $testAction = $args->getObject();

        $testActionType = $testAction->getType();
        $testActionTypeName = $testActionType->getName();
        if (!isset(self::ACTIONS_TO_POINTS_TYPE_MAP[$testActionTypeName])) {
            return;
        }

        $pointsTypeName = self::ACTIONS_TO_POINTS_TYPE_MAP[$testActionTypeName];

        /** @var EntityManagerInterface $em */
        $em = $args->getEntityManager();

        $pointsTypeRepository = $em->getRepository(PointsType::class);
        /** @var PointsType $pointsType */
        $pointsType = $pointsTypeRepository->findOneBy(['name' => $pointsTypeName]);

        $pointsEntity = new Points();
        $pointsEntity->setUser($testAction->getUser());
        $pointsEntity->setTest($testAction->getTest());
        $pointsEntity->setHint($testAction->getHint());
        $pointsEntity->setType($pointsType);
        $pointsEntity->setPoints($pointsType->getPoints());

        $em->persist($pointsEntity);
        $em->flush();
    }
}