<?php

namespace App\Controller\V1\Auth;

use App\Entity\User;
use App\Service\AuthOperation\VerificationProcessor;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;

/**
 * @Route("/account", name="account.")
 */
class VerificationController extends AbstractFOSRestController
{
    /**
     * @Post("/verify/{user}/{code}/", name="verify")
     */
    public function verify(User $user, string $code, VerificationProcessor $verificationProcessor)
    {
        try {
            $verificationProcessor->completeOperationProcess($user, $code);
        } catch (\RuntimeException $e) {
            return $this->view(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->view(null, Response::HTTP_OK);
    }
}
