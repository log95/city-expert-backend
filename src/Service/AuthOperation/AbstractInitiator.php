<?php

namespace App\Service\AuthOperation;

use App\Entity\AuthOperation;
use App\Entity\User;
use App\Service\FrontendLinkService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractInitiator
{
    private EntityManagerInterface $em;
    private CodeGenerator $codeGenerator;
    private FrontendLinkService $frontendLinkService;

    protected MailerInterface $mailer;
    protected TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        CodeGenerator $codeGenerator,
        MailerInterface $mailer,
        TranslatorInterface $translator,
        FrontendLinkService $frontendLinkService
    ) {
        $this->em = $em;
        $this->codeGenerator = $codeGenerator;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->frontendLinkService = $frontendLinkService;
    }

    abstract public function getType(): string;

    abstract protected function notifyUser(User $user, string $secretLink): void;

    public function init(User $user)
    {
        $authOperationRepository = $this->em->getRepository(AuthOperation::class);

        $operationType = $this->getType();

        $operation = $authOperationRepository->findOneBy([
            'user' => $user,
            'type' => $operationType,
        ]);

        if ($operation) {
            $this->em->remove($operation);
        }

        $secretCode = $this->codeGenerator->generateCode();

        $newOperation = new AuthOperation();
        $newOperation->setUser($user);
        $newOperation->setType($operationType);
        $newOperation->setCode($secretCode);

        $this->em->persist($newOperation);
        $this->em->flush();

        $link = $this->frontendLinkService->getAuthOperationUrl($operationType, $user->getId(), $secretCode);

        $this->notifyUser($user, $link);
    }
}
