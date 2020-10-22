<?php

namespace App\Service\AuthOperation;

use App\Entity\User;
use App\Enum\AuthOperationType;
use Symfony\Component\Mime\Email;

class VerificationInitiator extends AbstractInitiator
{
    public function getType(): string
    {
        return AuthOperationType::VERIFICATION;
    }

    protected function notifyUser(User $user, string $secretLink): void
    {
        $email = (new Email())
            ->to($user->getEmail())
            ->subject($this->translator->trans('VERIFICATION_EMAIL.TITLE'))
            ->text($this->translator->trans('VERIFICATION_EMAIL.BODY', ['#link#' => $secretLink]));


        $this->mailer->send($email);
    }
}
