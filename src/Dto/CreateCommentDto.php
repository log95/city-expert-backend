<?php

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCommentDto
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(max = 1000)
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    private string $message;

    public function getMessage(): string
    {
        return $this->message;
    }
}
