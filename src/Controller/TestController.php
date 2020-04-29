<?php

namespace App\Controller;

use App\Dto\CreateTestDto;
use App\Dto\UserAnswerDto;
use App\Entity\City;
use App\Entity\Test;
use App\Repository\TestRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * @Get("/tests/{test}/", name="test.show")
     */
    public function show(Test $test)
    {
        $repository = $this->getDoctrine()->getRepository(Test::class);

        [$prevTestId, $nextTestId] = $repository->getNearTests($test);

        $result = [
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'nears_tests' => [
                'prev' => $prevTestId,
                'next' => $nextTestId,
            ],
        ];

        return $this->view($result);
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

        return $this->view(['is_right_answer' => $isRightAnswer]);
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
