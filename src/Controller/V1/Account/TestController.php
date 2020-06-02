<?php

namespace App\Controller\V1\Account;

use App\Dto\CreateTestDto;
use App\Entity\City;
use App\Entity\Enum\TestPublishStatus;
use App\Entity\Enum\TestTransition;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
use App\Repository\TestRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Workflow\Registry;
use FOS\RestBundle\Controller\Annotations\Route;

/**
 * @Route("/account", name="account.")
 */
class TestController extends AbstractFOSRestController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Get("/tests/", name="test.list")
     */
    public function index(TestRepository $testRepository)
    {
        /** @var User $moderator */
        $moderator = $this->getUser();

        $result = $testRepository->getTestListForAccount($moderator);

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Get("/tests/{test}/", name="test.show")
     */
    public function show(Test $test)
    {
        if ($test->getCreatedBy() !== $this->getUser()) {
            return $this->view(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
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
            'country_id' => $test->getCity()->getCountry()->getId(),
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'answer' => $test->getAnswer(),
            'hints' => $hints,
            'status' => $test->getCurrentStatus(),
        ];

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Post("/tests/", name="test.save")
     *
     * @ParamConverter("createTestDto", converter="fos_rest.request_body")
     */
    public function save(
        CreateTestDto $createTestDto,
        Registry $workflowRegistry,
        ConstraintViolationListInterface $validationErrors
    ) {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();
        try {
            $cityRef = $em->getReference(City::class, $createTestDto->getCityId());

            $test = new Test();
            $test->setQuestion($createTestDto->getQuestion());
            $test->setAnswer($createTestDto->getAnswer());
            $test->setImageUrl($createTestDto->getImageUrl());
            $test->setCreatedBy($user);
            $test->setCity($cityRef);
            $test->setCurrentStatus(TestPublishStatus::NEW);

            $hintsText = $createTestDto->getHints();
            foreach ($hintsText as $hintText) {
                $hint = new TestHint($test, $hintText);

                $em->persist($hint);
            }

            $em->persist($test);
            $em->flush();

            $workflow = $workflowRegistry->get($test);
            $workflow->apply($test, TestTransition::TO_REVIEW);
            $em->flush();

            $em->getConnection()->commit();
        } catch (\Throwable $e) {
            $em->getConnection()->rollBack();

            $this->logger->error($e->getMessage());

            return $this->view(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->view(null, Response::HTTP_CREATED);
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