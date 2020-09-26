<?php

namespace App\Controller\V1;

use App\Enum\TestStatus;
use App\Entity\PointsType;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use App\Entity\TestHint;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Response;

class TestHintController extends AbstractFOSRestController
{
    /**
     * @Get("/hints/{hint}/", name="hint.show")
     */
    public function show(TestHint $hint)
    {
        /** @var User $user */
        $user = $this->getUser();

        $test = $hint->getTest();

        $em = $this->getDoctrine()->getManager();

        $testActionRepository = $em->getRepository(TestAction::class);

        $testStatus = $testActionRepository->getStatus($user, $test);
        if ($testStatus !== TestStatus::IN_PROCESS) {
            return $this->view(['text' => $hint->getText()], Response::HTTP_OK);
        }

        $isUsedHint = $testActionRepository->isUsedHint($user, $test, $hint);
        if ($isUsedHint) {
            return $this->view(['text' => $hint->getText()], Response::HTTP_OK);
        }

        $testActionTypeRepository = $em->getRepository(TestActionType::class);

        /** @var TestActionType $actionType */
        $actionType = $testActionTypeRepository->findOneBy(['name' => TestActionType::SHOW_HINT]);

        $testAction = new TestAction($user, $test, $actionType, $hint);
        $em->persist($testAction);
        $em->flush();

        $result = [
            'text' => $hint->getText(),
            'points' => PointsType::POINTS_MAP[PointsType::SHOW_HINT],
        ];

        return $this->view($result, Response::HTTP_OK);
    }
}