<?php

namespace App\Security\Voter;

use App\Entity\Test;
use App\Entity\User;
use App\Enum\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

// TODO: в итоге будем использовать или нет? или будем getModerator === moderator
class TestVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [Permission::MODERATION_VIEW, Permission::MODERATION_EDIT])) {
            return false;
        }

        if (!$subject instanceof Test) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        /** @var Test $test */
        $test = $subject;

        switch ($attribute)
        {
            case Permission::MODERATION_VIEW:
                return $this->canView($test, $user);
            case Permission::MODERATION_EDIT:
                return $this->canEdit($test, $user);
            default:
                throw new \LogicException('Permission is not defined.');
        }
    }

    private function canView(Test $test, User $user)
    {
        /*// if they can edit, they can view
        if ($this->canEdit($post, $user)) {
            return true;
        }

        // the Post object could have, for example, a method `isPrivate()`
        return !$post->isPrivate();*/
    }

    private function canEdit(Test $test, User $user)
    {
        return $user === $test->getModerator();
    }
}