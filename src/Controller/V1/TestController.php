<?php

namespace App\Controller\V1;

use App\Dto\UserAnswerDto;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\TestInterest;
use App\Entity\User;
use App\Repository\TestRepository;
use App\Service\PointsService;
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
    public function index(TestRepository $testRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        $tests = $testRepository->getAllTests($user);

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

        $testStatus = $testRepository->getStatus($user, $test);

        $interestRepository = $this->getDoctrine()->getRepository(TestInterest::class);

        $likesCount = $interestRepository->getCount($test, true);
        $dislikesCount = $interestRepository->getCount($test, false);

        $isCurrentUserLiked = $interestRepository->isUserLiked($user, $test);

        $hints = $test->getHints();

        if ($hints) {
            $hintsMap = [];
            foreach ($hints as $hint) {
                $hintsMap[$hint->getId()] = $hint->getText();
            }

            $hintIds = array_keys($hintsMap);

            if ($testStatus === Test::STATUS_IN_PROCESSING) {
                $hintRepository = $this->getDoctrine()->getRepository(TestHint::class);
                $hintForShowIds = $hintRepository->getUsedHintIds($user, $test);

                $hintForShowWithText = array_intersect_key($hintsMap, array_flip($hintForShowIds));
            } else {
                $hintForShowWithText = $hintsMap;
            }
        } else {
            $hintIds = [];
            $hintForShowWithText = [];
        }

        $result = [
            'id' => $test->getId(),
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'status' => $testStatus,
            'answer' => ($testStatus !== Test::STATUS_IN_PROCESSING) ? $test->getAnswer() : null,
            'hints' => [
                'all_ids' => $hintIds,
                'available' => $hintForShowWithText,
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
        Test $test,
        PointsService $pointsService
    ) {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $isRightAnswer = $test->isRightAnswer($answerDto->getAnswer());

        /** @var User $user */
        $user = $this->getUser();

        $points = $isRightAnswer ?
            $pointsService->addForCorrectAnswer($user, $test) :
            $pointsService->reduceForWrongAnswer($user, $test);

        $result = [
            'is_right_answer' => $isRightAnswer,
            'points' => $points,
        ];

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Get("/tests/{test}/answer/", name="test.show.answer")
     */
    public function showAnswer(Test $test, PointsService $pointsService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $points = $pointsService->reduceForShowAnswer($user, $test);

        $result = [
            'answer' => $test->getAnswer(),
            'points' => $points,
        ];

        return $this->view($result, Response::HTTP_OK);
    }
}
