<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ModeratorService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function determineModeratorForTest(): User
    {
        $userRepository = $this->em->getRepository(User::class);

        $moderator = $userRepository->getMostFreeModerator();
        if (!$moderator) {
            throw new \RuntimeException('No moderator found.');
        }

        return $moderator;
    }
}
