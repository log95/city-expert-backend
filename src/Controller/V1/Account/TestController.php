<?php

declare(strict_types=1);

namespace App\Controller\V1\Account;

use App\Dto\CreateTestDto;
use App\Dto\UpdateTestDto;
use App\Entity\City;
use App\Enum\TestPublishStatus;
use App\Enum\TestTransition;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
use App\Exceptions\FilterException;
use App\Repository\TestActionRepository;
use App\Repository\TestRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Registry;
use FOS\RestBundle\Controller\Annotations\Route;

/**
 * @Route("/account/tests", name="account.tests.")
 */
class TestController extends AbstractFOSRestController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Get("/", name="TestRepository")
     * @param TestActionRepository $testRepository
     * @param Request $request
     * @return View
     */
    public function index(TestRepository $testRepository, Request $request): View
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $tests = $testRepository->getCreatedTests(
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
     */
    public function show(Test $test): View
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
     * @Post("/", name="save")
     *
     * @ParamConverter("createTestDto", converter="fos_rest.request_body")
     * @param CreateTestDto $createTestDto
     * @param Registry $workflowRegistry
     * @param ConstraintViolationListInterface $validationErrors
     * @return View
     */
    public function save(
        CreateTestDto $createTestDto,
        Registry $workflowRegistry,
        ConstraintViolationListInterface $validationErrors
    ): View {
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
     * @Patch("/{test}/to-correction/")
     */
    public function returnToCorrection(Test $test, Registry $workflowRegistry)
    {
        /** @var User $user */
        $user = $this->getUser();

        // TODO: в гард
        if ($user->getId() !== $test->getCreatedBy()->getId()) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }

        $em = $this->getDoctrine()->getManager();

        $workflow = $workflowRegistry->get($test);

        try {
            $workflow->apply($test, TestTransition::BACK_TO_CORRECTION);
            $em->flush();
        } catch (NotEnabledTransitionException $e) {
            return $this->view(null, Response::HTTP_BAD_REQUEST);
        }

        return $this->view(null, Response::HTTP_OK);
    }

    /**
     * @Put("/{test}/", name="update")
     *
     * @ParamConverter("updateTestDto", converter="fos_rest.request_body")
     */
    public function update(
        Test $test,
        Registry $workflowRegistry,
        UpdateTestDto $updateTestDto,
        ConstraintViolationListInterface $validationErrors
    ) {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getId() !== $test->getCreatedBy()->getId()) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }

        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();
        try {
            $cityRef = $em->getReference(City::class, $updateTestDto->getCityId());

            $test->setQuestion($updateTestDto->getQuestion());
            $test->setAnswer($updateTestDto->getAnswer());
            $test->setCity($cityRef);

            if ($updateTestDto->getImageUrl()) {
                $test->setImageUrl($updateTestDto->getImageUrl());
            }

            $oldHints = $test->getHints();
            foreach ($oldHints as $oldHint) {
                $em->remove($oldHint);
            }

            $hintsText = $updateTestDto->getHints();
            foreach ($hintsText as $hintText) {
                $hint = new TestHint($test, $hintText);
                $em->persist($hint);
            }

            $workflow = $workflowRegistry->get($test);
            $workflow->apply($test, TestTransition::TO_REVIEW);

            $em->flush();

            $em->getConnection()->commit();
        } catch (\Throwable $e) {
            $em->getConnection()->rollBack();

            $this->logger->error($e->getMessage());

            return $this->view(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->view(null, Response::HTTP_OK);
    }
}
