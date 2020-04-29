<?php

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class NewTestInterestDto
{
    /**
     * @Assert\NotNull
     * @Assert\Type("bool")
     * @Serializer\Type("boolean")
     */
    private bool $isLiked;

    /**
     * NewTestInterestDto constructor.
     * @param bool $isLiked
     */
    public function __construct(bool $isLiked)
    {
        $this->isLiked = $isLiked;
    }

    /**
     * @return bool
     */
    public function isLiked(): bool
    {
        return $this->isLiked;
    }
}