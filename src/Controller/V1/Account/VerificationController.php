<?php

namespace App\Controller\V1\Account;

use App\Entity\AuthOperation;
use App\Entity\User;
use App\Enum\AuthOperationType;
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
    public function verify(User $user, string $code)
    {
        if ($user->isVerified()) {
            return $this->view(['Error' => 'User is already verified.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$code) {
            return $this->view(['Error' => 'Empty code.'], Response::HTTP_BAD_REQUEST);
        }

        //$verificationService->isValidCode();

        // подтверждаем пользователя - дату
        // удаляем код
        // на какое-то событие начилсяем очки.

        $em = $this->getDoctrine()->getManager();
        $authOperationRepository = $em->getRepository(AuthOperation::class);

        $verificationOperation = $authOperationRepository->findOneBy([
            'user' => $user,
            'type' => AuthOperationType::VERIFICATION,
            'code' => $code,
        ]);

        if (!$verificationOperation) {
            return $this->view(['Error' => 'Wrong code or code is expired.'], Response::HTTP_BAD_REQUEST);
        }
    }
}