<?php

namespace App\Service\AuthOperation;

use App\Entity\User;
use App\Enum\AuthOperationType;

class VerificationProcessor extends AbstractProcessor
{
    public function getType(): string
    {
        return AuthOperationType::VERIFICATION;
    }

    protected function completeOperationSpecificActions(User $user, array $data): void
    {
        $user->markVerified();

        $this->em->persist($user);
        $this->em->flush();
    }
}
