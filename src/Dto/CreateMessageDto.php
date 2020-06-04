<?php

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CreateMessageDto
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(max = 1000)
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    private string $message;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}