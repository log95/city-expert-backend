<?php

namespace App\Controller;

use App\Dto\CreateTestDto;
use App\Dto\UserAnswerDto;
use App\Entity\City;
use App\Entity\Test;
use App\Entity\TestInterest;
use App\Entity\User;
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

        $test = new Test();
        $test->setQuestion($createTestDto->getQuestion());
        $test->setAnswer($createTestDto->getAnswer());
        $test->setImageUrl($createTestDto->getImageUrl());
        $test->setHints($createTestDto->getHints());
        $test->setCity($cityRef);

        $em->persist($test);
        $em->flush();

        return $this->view(null, Response::HTTP_CREATED);
    }

    /**
     * @Get("/tests/{id}/", name="test.show")
     */
    public function show(Test $test)
    {
        $testRepository = $this->getDoctrine()->getRepository(Test::class);
        $nearTests = $testRepository->getNearTests($test);

        $testInterestRepository = $this->getDoctrine()->getRepository(TestInterest::class);

        $likesCount = $testInterestRepository->getCount($test, true);
        $dislikesCount = $testInterestRepository->getCount($test, false);

        /** @var User $user */
        $user = $this->getUser();
        $isCurrentUserLiked = $testInterestRepository->isUserLiked($user, $test);

        $result = [
            'id' => $test->getId(),
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
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
     * @Post("/tests/{test}/answer/", name="test.answer")
     *
     * @ParamConverter("answerDto", converter="fos_rest.request_body")
     */
    public function answer(UserAnswerDto $answerDto, ConstraintViolationListInterface $validationErrors, Test $test)
    {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $isRightAnswer = $test->isRightAnswer($answerDto->getAnswer());

        return $this->view(['is_right_answer' => $isRightAnswer], Response::HTTP_OK);
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
