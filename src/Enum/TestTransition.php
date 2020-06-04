<?php

namespace App\Entity\Enum;

class TestTransition
{
    const TO_REVIEW = 'to_review';
    const APPROVE = 'approve';
    const REJECT = 'reject';
    const BACK_TO_CORRECTION = 'back_to_correction';
}
