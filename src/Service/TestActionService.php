<?php

namespace App\Service;

use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

// TODO: remove
class TestActionService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addShowHintAction(User $user, Test $test, TestHint $hint)
    {
        
    }

    public function addWrongAnswerAction()
    {

    }

    public function addCorrectAnswerAction()
    {

    }

    public function addShowAnswerAction()
    {

    }

    public function addAction()
    {

    }
}