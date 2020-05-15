<?php

namespace App\Controller\V1;

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

/**
 * @Route("/moderation", name="moderation.")
 */
class ModeratorController extends AbstractFOSRestController
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
     */
    public function showTest(Test $test)
    {
        $this->denyAccessUnlessGranted(Role::MODERATOR);

        $actions = [];
        if ($test->getModerator() === $this->getUser()) {
            $actions = [TestTransition::APPROVE, TestTransition::REJECT];
        }

        $hints = $test->getHints()->map(function (TestHint $hint) {
            return [
                'id' => $hint->getId(),
                'text' => $hint->getText(),
            ];
        });

        $result = [
            'id' => $test->getId(),
            'city_id' => $test->getCity()->getId(),
            'country_id_id' => $test->getCity()->getCountry()->getId(),
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'answer' => $test->getAnswer(),
            'hints' => $hints,
            'status' => $test->getCurrentStatus(),
            'actions' => $actions,
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

        try {
            $workflow = $workflowRegistry->get($test);

            $workflow->apply($test, TestTransition::APPROVE);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        } catch (NotEnabledTransitionException $e) {
            return $this->view(['error' => 'Can not make transition.'], Response::HTTP_BAD_REQUEST);
        }

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