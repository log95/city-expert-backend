<?php

namespace App\Controller\V1;

use App\Dto\UserAnswerDto;
use App\Entity\Enum\TestStatus;
use App\Entity\PointsType;
use App\Entity\Test;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use App\Entity\TestHint;
use App\Entity\TestInterest;
use App\Entity\User;
use App\Repository\TestActionRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TestController extends AbstractFOSRestController
{
    /**
     * @Get("/tests/", name="test.list")
     */
    public function index(TestActionRepository $testActionRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        $tests = $testActionRepository->getAllTestsWithStatus($user);

        return $this->view($tests, Response::HTTP_OK);
    }

    /**
     * @Get("/tests/{test}/", name="test.show")
     */
    public function show(Test $test)
    {
        $testRepository = $this->getDoctrine()->getRepository(Test::class);
        $nearTests = $testRepository->getNearTests($test);

        /** @var User $user */
        $user = $this->getUser();

        $testActionRepository = $this->getDoctrine()->getRepository(TestAction::class);
        $testStatus = $testActionRepository->getStatus($user, $test);

        $interestRepository = $this->getDoctrine()->getRepository(TestInterest::class);

        $likesCount = $interestRepository->getCount($test, true);
        $dislikesCount = $interestRepository->getCount($test, false);

        $isCurrentUserLiked = $interestRepository->isUserLiked($user, $test);

        $hintIds = array_map(fn (TestHint $hint) => $hint->getId(), $test->getHints());
        $hintForShowIds = $testStatus === TestStatus::IN_PROCESS ?
            $testActionRepository->getUsedHintIds($user, $test) :
            $hintIds;

        $result = [
            'id' => $test->getId(),
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'status' => $testStatus,
            'answer' => ($testStatus !== Test::STATUS_IN_PROCESSING) ? $test->getAnswer() : null,
            'hints' => [
                'all_ids' => $hintIds,
                'available_ids' => $hintForShowIds,
            ],
            'near_tests' => [
                'prev' => $nearTests['prev'],
                'next' => $nearTests['next'],
            ],
            'interest' => [
                'likes_count' => $likesCount,
                'dislikes_count' => $dislikesCount,
                'current_user_liked' => $isCurrentUserLiked,
            ],
        ];

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Post("/tests/{test}/answer/", name="test.answer.attempt")
     *
     * @ParamConverter("answerDto", converter="fos_rest.request_body")
     */
    public function attemptAnswer(
        UserAnswerDto $answerDto,
        ConstraintViolationListInterface $validationErrors,
        Test $test
    ) {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $testActionRepository = $em->getRepository(TestAction::class);
        $testStatus = $testActionRepository->getStatus($user, $test);

        if ($testStatus !== TestStatus::IN_PROCESS) {
            return $this->view(['error' => 'Test status is not processing.'], Response::HTTP_BAD_REQUEST);
        }

        $isRightAnswer = $test->isRightAnswer($answerDto->getAnswer());

        $actionTypeName = $isRightAnswer ? TestActionType::CORRECT_ANSWER : TestActionType::WRONG_ANSWER;

        $testActionTypeRepository = $em->getRepository(TestActionType::class);

        /** @var TestActionType $actionType */
        $actionType = $testActionTypeRepository->findOneBy(['name' => $actionTypeName]);

        $testAction = new TestAction($user, $test, $actionType);
        $em->persist($testAction);
        $em->flush();

        $result = [
            'is_right_answer' => $isRightAnswer,
            'points' => $isRightAnswer ? PointsType::POINTS_MAP[PointsType::CORRECT_ANSWER] : null,
        ];

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Get("/tests/{test}/answer/", name="test.show.answer")
     */
    public function showAnswer(Test $test)
    {
        /** @var User $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $testActionRepository = $em->getRepository(TestAction::class);
        $testStatus = $testActionRepository->getStatus($user, $test);

        if ($testStatus !== TestStatus::IN_PROCESS) {
            return $this->view(['error' => 'Test status is not processing.'], Response::HTTP_BAD_REQUEST);
        }

        $testActionTypeRepository = $em->getRepository(TestActionType::class);

        /** @var TestActionType $actionType */
        $actionType = $testActionTypeRepository->findOneBy(['name' => TestActionType::SHOW_ANSWER]);

        $testAction = new TestAction($user, $test, $actionType);
        $em->persist($testAction);
        $em->flush();

        $result = [
            'answer' => $test->getAnswer(),
            'points' => PointsType::POINTS_MAP[PointsType::SHOW_ANSWER],
        ];

        return $this->view($result, Response::HTTP_OK);
    }
}
