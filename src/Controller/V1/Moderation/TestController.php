<?php

namespace App\Controller\V1\Moderation;

use App\Entity\Enum\TestPublishStatus;
use App\Entity\Enum\TestTransition;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
use App\Enum\Role;
use App\Repository\TestRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;

/**
 * @Route("/moderation", name="moderation.")
 */
class TestController extends AbstractFOSRestController
{
    /**
     * @Get("/tests/", name="test.list")
     */
    public function index(TestRepository $testRepository)
    {
        $this->denyAccessUnlessGranted(Role::MODERATOR);

        /** @var User $moderator */
        $moderator = $this->getUser();

        $result = $testRepository->getTestListForModerator($moderator);

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Get("/tests/{test}/", name="test.show")
     *
     * TODO: подумать над объединением метода просмотра теста модератора и создающего.
     */
    public function show(Test $test, Registry $workflowRegistry)
    {
        $this->denyAccessUnlessGranted(Role::MODERATOR);

        $transitions = [];
        if ($test->getModerator() === $this->getUser() &&
            $test->getCurrentStatus() === TestPublishStatus::REVIEWED
        ) {
            $workflow = $workflowRegistry->get($test);
            $enabledTransitions = $workflow->getEnabledTransitions($test);

            $transitions = array_map(function (Transition $transition) {
                return $transition->getName();
            }, $enabledTransitions);
        }

        $hints = $test->getHints()->map(function (TestHint $hint) {
            return [
                'id' => $hint->getId(),
                'text' => $hint->getText(),
            ];
        });

        $result = [
            'id' => $test->getId(),
            'city' => [
                'id' => $test->getCity()->getId(),
                'name' => $test->getCity()->getName(),
            ],
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'answer' => $test->getAnswer(),
            'hints' => $hints,
            'status' => $test->getCurrentStatus(),
            'transitions' => $transitions,
        ];

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Post("/tests/{test}/approve/", name="test.approve")
     */
    public function approve(Test $test, Registry $workflowRegistry)
    {
        if ($test->getModerator() !== $this->getUser()) {
            return $this->view(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        $workflow = $workflowRegistry->get($test);

        if (!$workflow->can($test, TestTransition::APPROVE)) {
            return $this->view(['error' => 'Can not make transition.'], Response::HTTP_BAD_REQUEST);
        }

        $workflow->apply($test, TestTransition::APPROVE);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->view(null, Response::HTTP_OK);
    }

    /**
     * @Post("/tests/{test}/reject/", name="test.reject")
     */
    public function reject(Test $test, Registry $workflowRegistry)
    {
        // TODO: проверить, что он сообщение указал.

        if ($test->getModerator() !== $this->getUser()) {
            return $this->view(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $workflow = $workflowRegistry->get($test);

            $workflow->apply($test, TestTransition::REJECT);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        } catch (NotEnabledTransitionException $e) {
            return $this->view(['error' => 'Can not make transition.'], Response::HTTP_BAD_REQUEST);
        }

        return $this->view(null, Response::HTTP_OK);
    }
}
