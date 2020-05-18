<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotVerifiedEmail extends Constraint
{
    public $message = 'User with such email is already registered.';

    public function validatedBy()
    {
        return NotVerifiedEmailValidator::class;
    }
}