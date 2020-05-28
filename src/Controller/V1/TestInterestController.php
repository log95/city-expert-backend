<?php

namespace App\Controller\V1;

use App\Dto\NewTestInterestDto;
use App\Entity\Test;
use App\Entity\TestInterest;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Post;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TestInterestController extends AbstractFOSRestController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Post("/test_interests/{test}/", name="test.interest.new")
     *
     * @ParamConverter("interestDto", converter="fos_rest.request_body")
     */
    public function createOrUpdate(
        Test $test,
        NewTestInterestDto $interestDto,
        ConstraintViolationListInterface $validationErrors
    ) {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $interestRepository = $this->getDoctrine()->getRepository(TestInterest::class);

        try {
            $interestRepository->createOrUpdate($user, $test, $interestDto->isLiked());
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());

            return $this->view(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->view(null, Response::HTTP_OK);
    }

    /**
     * @Delete("/test_interests/{test}/", name="test.interest.delete")
     */
    public function delete(Test $test)
    {
        /** @var User $user */
        $user = $this->getUser();

        $interestRepository = $this->getDoctrine()->getRepository(TestInterest::class);

        $testInterest = $interestRepository->findOneBy([
            'user' => $user,
            'test' => $test,
        ]);

        if (!$testInterest) {
            return $this->view(['error' => 'Interest for such user and test is not found.'], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();

        $em->remove($testInterest);
        $em->flush();

        return $this->view(null, Response::HTTP_OK);
    }
}