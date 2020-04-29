<?php

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserAnswerDto
{
    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    private string $answer;

    /**
     * UserAnswerDto constructor.
     * @param string $answer
     */
    public function __construct(string $answer)
    {
        $this->answer = $answer;
    }

    /**
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }
}