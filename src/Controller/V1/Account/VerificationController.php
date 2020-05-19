<?php

namespace App\Controller\V1\Account;

use App\Entity\User;
use App\Service\AuthOperation\VerificationProcessor;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;

/**
 * @Route("/account", name="account.")
 */
class VerificationController extends AbstractFOSRestController
{
    /**
     * @Get("/verify/{user}/{code}/", name="verify")
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