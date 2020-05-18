<?php

namespace App\Controller\V1\Account;

use App\Dto\RegisterUserDto;
use App\Entity\User;
use App\Service\AuthOperation\VerificationInitiator;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use FOS\RestBundle\Controller\Annotations\Route;

/**
 * @Route("/account", name="account.")
 */
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
        VerificationInitiator $verificationInitiator
    ) {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository(User::class);

        $user = $userRepository->findOneBy(['email' => $userDto->getEmail()]);

        if (!$user) {
            $user = new User();
            $user->setEmail($userDto->getEmail());
        }

        $user->setPassword($encoder->encodePassword($user, $userDto->getPassword()));

        $em->persist($user);
        $em->flush();

        // TODO: то что разные em-ы транзакции не сломаются?
        $verificationInitiator->init($user);

        // TODO: это после подтверждения.
        //$pointsService->addForRegistration($user);

        return $this->view(null, Response::HTTP_CREATED);
    }
}