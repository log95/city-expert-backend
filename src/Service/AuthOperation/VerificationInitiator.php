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
            ->subject('Registration.')
            ->text(sprintf('Follow link %s to confirm registration.', $secretLink));


        $this->mailer->send($email);
    }
}
