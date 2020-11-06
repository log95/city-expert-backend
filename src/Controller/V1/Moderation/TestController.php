<?php

declare(strict_types=1);

namespace App\Controller\V1\Moderation;

use App\Enum\TestPublishStatus;
use App\Enum\TestTransition;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
use App\Enum\Role;
use App\Exceptions\FilterException;
use App\Repository\TestRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\TransitionBlocker;

/**
 * @Route("/moderation/tests", name="moderation.tests.")
 */
class TestController extends AbstractFOSRestController
{
    /**
     * @Get("/", name="list")
     * @param TestRepository $testRepository
     * @param Request $request
     * @return View
     */
    public function index(TestRepository $testRepository, Request $request): View
    {
        $this->denyAccessUnlessGranted(Role::MODERATOR);

        /** @var User $moderator */
        $moderator = $this->getUser();

        try {
            $tests = $testRepository->getTestsForModeration(
                $moderator,
                $request->query->getInt('page'),
                $request->query->getInt('per_page'),
                $request->get('sort_by'),
                $request->get('sort_direction'),
                $request->get('filter_by'),
            );

            return $this->view($tests, Response::HTTP_OK);
        } catch (FilterException $e) {
            return $this->view(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Get("/tests/{test}/", name="test.show")
     *
     * TODO: подумать над объединением метода просмотра теста модератора и создающего.
     *
     * TODO: remove
     */
    public function show(Test $test, Registry $workflowRegistry)
    {
        $this->denyAccessUnlessGranted(Role::MODERATOR);

        // TODO: подумать над переносом этого в гард, тогда у не модератора никаких действий не будет.
        $transitions = [];
        if ($test->getModerator() === $this->getUser() &&
            $test->getCurrentStatus() === TestPublishStatus::REVIEWED
        ) {
            $transitions = [
                TestTransition::REJECT,
                TestTransition::PUBLISH,
            ];

            /*$workflow = $workflowRegistry->get($test);
            $enabledTransitions = $workflow->getEnabledTransitions($test);

            $transitions = array_map(function (Transition $transition) {
                return $transition->getName();
            }, $enabledTransitions);*/
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
            'chat_id' => $test->getChat()->getId(),
            'transitions' => $transitions,
        ];

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Post("/{test}/approve/", name="approve")
     */
    public function approve(Test $test, Registry $workflowRegistry)
    {
        if ($test->getModerator() !== $this->getUser()) {
            return $this->view(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        $workflow = $workflowRegistry->get($test);

        if (!$workflow->can($test, TestTransition::PUBLISH)) {
            return $this->view(['error' => 'Can not make transition.'], Response::HTTP_BAD_REQUEST);
        }

        $workflow->apply($test, TestTransition::PUBLISH);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->view(null, Response::HTTP_OK);
    }

    /**
     * @Post("/{test}/reject/", name="reject")
     */
    public function reject(Test $test, Registry $workflowRegistry)
    {
        // TODO: может это всё в гард?
        if ($test->getModerator() !== $this->getUser()) {
            return $this->view(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $workflow = $workflowRegistry->get($test);

            $workflow->apply($test, TestTransition::REJECT);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        } catch (NotEnabledTransitionException $e) {
            /** @var TransitionBlocker $blocker */
            $blocker = $e->getTransitionBlockerList()->getIterator()->current();

            return $this->view([
                'type' => $blocker->getCode(),
                'message' => $blocker->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->view(null, Response::HTTP_OK);
    }
}
