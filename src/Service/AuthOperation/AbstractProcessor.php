<?php

namespace App\Service\AuthOperation;

use App\Entity\AuthOperation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractProcessor
{
    private int $operationTtl;

    protected EntityManagerInterface $em;

    public function __construct(int $operationTtl, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->operationTtl = $operationTtl;
    }

    abstract public function getType(): string;

    abstract protected function completeOperationSpecificActions(User $user, array $data): void;

    public function isValidCode(User $user, string $code): bool
    {
        $operationType = $this->getType();

        $authOperationRepository = $this->em->getRepository(AuthOperation::class);

        return $authOperationRepository->isValidCode($user, $operationType, $code, $this->operationTtl);
    }

    public function completeOperationProcess(User $user, string $code, array $data = []): void
    {
        if ($user->isVerified()) {
            throw new \RuntimeException('User is already verified.');
        }

        if (!$code || !$this->isValidCode($user, $code)) {
            throw new \RuntimeException('Wrong code.');
        }

        $authOperationRepository = $this->em->getRepository(AuthOperation::class);

        $authOperation = $authOperationRepository->findOneBy([
            'user' => $user,
            'type' => $this->getType(),
        ]);

        $this->em->remove($authOperation);
        $this->em->flush();

        $this->completeOperationSpecificActions($user, $data);
    }
}