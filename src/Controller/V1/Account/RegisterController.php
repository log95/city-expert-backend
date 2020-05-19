<?php

namespace App\Controller\V1\Account;

use App\Dto\RegisterUserDto;
use App\Entity\User;
use App\Service\AuthOperation\VerificationInitiator;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Log\LoggerInterface;
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
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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

        $em->getConnection()->beginTransaction();
        try {
            $user = $userRepository->findOneBy(['email' => $userDto->getEmail()]);

            if ($user) {
                $em->remove($user);
                $em->flush();
            }

            $newUser = new User();
            $newUser->setEmail($userDto->getEmail());
            $newUser->setPassword($encoder->encodePassword($newUser, $userDto->getPassword()));

            $em->persist($newUser);
            $em->flush();

            $verificationInitiator->init($newUser);

            $em->getConnection()->commit();
        } catch (\Throwable $e) {
            $em->getConnection()->rollBack();

            $this->logger->error($e->getMessage());

            return $this->view(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->view(null, Response::HTTP_CREATED);
    }
}