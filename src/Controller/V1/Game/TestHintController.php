<?php

namespace App\Controller\V1\Game;

use App\Enum\TestStatus;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use App\Entity\TestHint;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

class TestHintController extends AbstractFOSRestController
{
    /**
     * @Get("/hints/{hint}/", name="hint.show")
     * @param TestHint $hint
     * @return View
     */
    public function show(TestHint $hint): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $test = $hint->getTest();

        $em = $this->getDoctrine()->getManager();

        $testActionRepository = $em->getRepository(TestAction::class);

        $testStatus = $testActionRepository->getTestStatus($user, $test);
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

        return $this->view(['text' => $hint->getText()], Response::HTTP_OK);
    }
}
