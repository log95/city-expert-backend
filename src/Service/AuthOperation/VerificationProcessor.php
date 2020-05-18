<?php

namespace App\Service\AuthOperation;

use App\Entity\AuthOperation;
use App\Entity\User;
use App\Enum\AuthOperationType;
use App\Service\FrontendLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;

class VerificationProcessor extends AbstractProcessor
{
    public function getType(): string
    {
        return AuthOperationType::VERIFICATION;
    }
}