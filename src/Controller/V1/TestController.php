<?php

namespace App\Controller\V1;

use App\Dto\CreateTestDto;
use App\Dto\UserAnswerDto;
use App\Entity\City;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\TestInterest;
use App\Entity\User;
use App\Service\PointsService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TestController extends AbstractFOSRestController
{
    /**
     * @Post("/tests/", name="test.save")
     *
     * @ParamConverter("createTestDto", converter="fos_rest.request_body")
     */
    public function save(CreateTestDto $createTestDto, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $cityRef = $em->getReference(City::class, $createTestDto->getCityId());

        // TODO: sql inj, xss

        $test = new Test();
        $test->setQuestion($createTestDto->getQuestion());
        $test->setAnswer($createTestDto->getAnswer());
        $test->setImageUrl($createTestDto->getImageUrl());
        $test->setCity($cityRef);

        $hintsText = $createTestDto->getHints();
        foreach ($hintsText as $hintText) {
            $hint = new TestHint();
            $hint->setTest($test);
            $hint->setText($hintText);

            $em->persist($hint);
        }

        $em->persist($test);
        $em->flush();

        return $this->view(null, Response::HTTP_CREATED);
    }

    /**
     * @Get("/tests/{test}/", name="test.show")
     */
    public function show(Test $test)
    {
        $testRepository = $this->getDoctrine()->getRepository(Test::class);
        $nearTests = $testRepository->getNearTests($test);

        $interestRepository = $this->getDoctrine()->getRepository(TestInterest::class);

        $likesCount = $interestRepository->getCount($test, true);
        $dislikesCount = $interestRepository->getCount($test, false);

        /** @var User $user */
        $user = $this->getUser();
        $isCurrentUserLiked = $interestRepository->isUserLiked($user, $test);

        $hints = $test->getHints();

        if ($hints) {
            $hintRepository = $this->getDoctrine()->getRepository(TestHint::class);
            $usedHintIds = $hintRepository->getUsedHintIds($user, $test);

            $hintsMap = [];
            foreach ($hints as $hint) {
                $hintsMap[$hint->getId()] = $hint->getText();
            }

            $hintIds = array_keys($hintsMap);
            $viewedHintsWithText = array_intersect_key($hintsMap, array_flip($usedHintIds));
        } else {
            $hintIds = [];
            $viewedHintsWithText = [];
        }

        $result = [
            'id' => $test->getId(),
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'hints' => [
                'all_ids' => $hintIds,
                'viewed_info' => $viewedHintsWithText,
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
    public function attemptAnswer(UserAnswerDto $answerDto, ConstraintViolationListInterface $validationErrors, Test $test, PointsService $pointsService)
    {
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

    /**
     * @Put("/tests/{test}/", name="test.update")
     */
    public function update(Test $test)
    {

    }

    /**
     * @Delete("/tests/{test}/", name="test.delete")
     */
    public function delete(Test $test)
    {

    }
}
