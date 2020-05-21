<?php

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler as BaseAuthenticationSuccessHandler;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private AuthenticationSuccessHandlerInterface $baseHandler;

    public function __construct(BaseAuthenticationSuccessHandler $baseHandler)
    {
        $this->baseHandler = $baseHandler;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if (!$token->getUser()->isVerified()) {
            return new JsonResponse('Not verified email.', Response::HTTP_FORBIDDEN);
        }

        return $this->baseHandler->onAuthenticationSuccess($request, $token);
    }
}
