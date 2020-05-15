<?php

namespace App\Service;

use App\Entity\Test;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ModeratorService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // TODO: потом сделать поиск самого свободного. strategy
    public function determineModeratorForTest(Test $test): User
    {
        $userRepository = $this->em->getRepository(User::class);

        $moderator = $userRepository->getFirstModerator();
        if (!$moderator) {
            throw new \RuntimeException('No moderator found.');
        }

        return $moderator;
    }
}