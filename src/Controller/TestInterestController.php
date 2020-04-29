<?php

namespace App\Controller;

use App\Dto\NewTestInterestDto;
use App\Entity\Test;
use App\Entity\TestInterest;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TestInterestController extends AbstractFOSRestController
{
    /**
     * @Post("/test_interests/{test}/", name="test.interest.new")
     *
     * @ParamConverter("interestDto", converter="fos_rest.request_body")
     */
    public function createOrUpdate(Test $test, NewTestInterestDto $interestDto, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $interestRepository = $this->getDoctrine()->getRepository(TestInterest::class);

        $testInterest = $interestRepository->getInterest($user, $test);
        if (!$testInterest) {
            $testInterest = new TestInterest();
            $testInterest->setUser($user);
            $testInterest->setTest($test);
        }

        // TODO: здесь при одновременных запросах возможно 2 записи будут в бд. Проверить со sleep

        $testInterest->setIsLiked($interestDto->isLiked());

        $em = $this->getDoctrine()->getManager();
        $em->persist($testInterest);
        $em->flush();

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

        $testInterest = $interestRepository->getInterest($user, $test);
        if (!$testInterest) {
            return $this->view(['Error' => 'Interest for such user and test is not found'], Response::HTTP_NOT_FOUND);
        }

        // TODO: на этом моменте может быть гонка и будет проба 2 раза удалить тест

        $em = $this->getDoctrine()->getManager();

        $em->remove($testInterest);
        $em->flush();

        return $this->view(null, Response::HTTP_OK);
    }
}