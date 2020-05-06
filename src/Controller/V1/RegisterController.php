<?php

namespace App\Controller\V1;

use App\Dto\RegisterUserDto;
use App\Entity\User;
use App\Service\PointsService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RegisterController extends AbstractFOSRestController
{
    /**
     * @Post("/register/", name="register")
     *
     * @ParamConverter("userDto", converter="fos_rest.request_body")
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return \FOS\RestBundle\View\View
     */
    public function register(
        RegisterUserDto $userDto,
        ConstraintViolationListInterface $validationErrors,
        UserPasswordEncoderInterface $encoder,
        PointsService $pointsService
    ) {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($userDto->getEmail());
        $user->setPassword($encoder->encodePassword($user, $userDto->getPassword()));

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $pointsService->addForRegistration($user);

        return $this->view(null, Response::HTTP_CREATED);
    }
}