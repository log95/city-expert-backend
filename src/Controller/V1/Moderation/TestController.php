<?php

declare(strict_types=1);

namespace App\Controller\V1\Moderation;

use App\Enum\TestTransition;
use App\Entity\Test;
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
     * @Post("/{test}/approve/", name="approve")
     * @param Test $test
     * @param Registry $workflowRegistry
     * @return View
     */
    public function approve(Test $test, Registry $workflowRegistry): View
    {
        try {
            $workflow = $workflowRegistry->get($test);

            $workflow->apply($test, TestTransition::PUBLISH);

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

    /**
     * @Post("/{test}/reject/", name="reject")
     * @param Test $test
     * @param Registry $workflowRegistry
     * @return View
     */
    public function reject(Test $test, Registry $workflowRegistry): View
    {
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
