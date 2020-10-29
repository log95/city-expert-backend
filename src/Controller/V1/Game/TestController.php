<?php

declare(strict_types=1);

namespace App\Controller\V1\Game;

use App\Dto\UserAnswerDto;
use App\Enum\TestStatus;
use App\Entity\Test;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use App\Entity\TestHint;
use App\Entity\TestInterest;
use App\Entity\User;
use App\Exceptions\FilterException;
use App\Exceptions\TestNotPublishedException;
use App\Repository\TestActionRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Route("/tests", name="tests.")
 */
class TestController extends AbstractFOSRestController
{
    /**
     * @Get("/", name="list")
     * @param TestActionRepository $testActionRepository
     * @param Request $request
     * @return View
     */
    public function index(TestActionRepository $testActionRepository, Request $request): View
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $tests = $testActionRepository->getTestListForUser(
                $user,
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
     * @Get("/{test}/", name="show")
     * @param Test $test
     * @return View
     */
    public function show(Test $test): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $testActionRepository = $this->getDoctrine()->getRepository(TestAction::class);
        try {
            $testStatus = $testActionRepository->getTestStatus($user, $test);
        } catch (TestNotPublishedException $e) {
            throw new BadRequestHttpException('TEST_NOT_PUBLISHED');
        }

        $testRepository = $this->getDoctrine()->getRepository(Test::class);
        $nearTests = $testRepository->getNearPublishedTests($test);

        $interestRepository = $this->getDoctrine()->getRepository(TestInterest::class);

        $interestCounts = $interestRepository->getCounts($test);
        $isCurrentUserLiked = $interestRepository->isUserLiked($user, $test);

        $hintIds = $test->getHints()->map(fn (TestHint $hint) => $hint->getId());
        $hintForShowIds = $testStatus === TestStatus::IN_PROCESS ?
            $testActionRepository->getUsedHintIds($user, $test) :
            $hintIds;

        $result = [
            'id' => $test->getId(),
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'status' => $testStatus,
            'answer' => ($testStatus !== TestStatus::IN_PROCESS) ? $test->getAnswer() : null,
            'hints' => [
                'all_ids' => $hintIds,
                'available_ids' => $hintForShowIds,
            ],
            'near_tests' => [
                'prev' => $nearTests['prev'],
                'next' => $nearTests['next'],
            ],
            'interest' => [
                'likes_count' => $interestCounts['likes'],
                'dislikes_count' => $interestCounts['dislikes'],
                'current_user_liked' => $isCurrentUserLiked,
            ],
        ];

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Post("/{test}/answer/", name="answer.attempt")
     *
     * @ParamConverter("answerDto", converter="fos_rest.request_body")
     *
     * @param UserAnswerDto $answerDto
     * @param ConstraintViolationListInterface $validationErrors
     * @param Test $test
     * @return View
     */
    public function attemptAnswer(
        UserAnswerDto $answerDto,
        ConstraintViolationListInterface $validationErrors,
        Test $test
    ): View {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $isRightAnswer = $test->isRightAnswer($answerDto->getAnswer());

        $actionTypeName = $isRightAnswer ? TestActionType::CORRECT_ANSWER : TestActionType::WRONG_ANSWER;

        $testActionTypeRepository = $em->getRepository(TestActionType::class);

        /** @var TestActionType $actionType */
        $actionType = $testActionTypeRepository->findOneBy(['name' => $actionTypeName]);

        $testAction = new TestAction($user, $test, $actionType);
        $em->persist($testAction);
        $em->flush();

        return $this->view(['is_right_answer' => $isRightAnswer], Response::HTTP_OK);
    }

    /**
     * @Get("/{test}/answer/", name="show-answer")
     * @param Test $test
     * @return View
     */
    public function showAnswer(Test $test): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $testActionTypeRepository = $em->getRepository(TestActionType::class);

        /** @var TestActionType $actionType */
        $actionType = $testActionTypeRepository->findOneBy(['name' => TestActionType::SHOW_ANSWER]);

        $testAction = new TestAction($user, $test, $actionType);
        $em->persist($testAction);
        $em->flush();

        return $this->view(['answer' => $test->getAnswer()], Response::HTTP_OK);
    }
}
