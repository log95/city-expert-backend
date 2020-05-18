<?php

namespace App\Service\AuthOperation;

use App\Entity\AuthOperation;
use App\Entity\User;
use App\Service\FrontendLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;

abstract class AbstractProcessor
{
    private EntityManagerInterface $em;
    private int $operationTtl;

    public function __construct(int $operationTtl, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->operationTtl = $operationTtl;
    }

    abstract public function getType(): string;

    //abstract public function notifyUser(User $user, string $secretLink): void;

    public function isValidCode(User $user, string $code): bool
    {
        $operationType = $this->getType();

        $authOperationRepository = $this->em->getRepository(AuthOperation::class);

        $authOperationRepository->isValidCode($user, $operationType, $code, $this->operationTtl);
    }

    public function completeOperationProcess(User $user, string $code): void
    {

    }
}