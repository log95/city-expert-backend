<?php

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CreateTestDto
{
    /**
     * @Assert\NotBlank(allowNull = true)
     * @Assert\Length(max = 255)
     * @Serializer\Type("string")
     */
    private ?string $question;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max = 255)
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    private string $answer;

    /**
     * @Assert\NotBlank(allowNull = true)
     * @Assert\Type(type = "array")
     * @Serializer\Type("array")
     */
    private ?array $hints;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max = 255)
     * @Assert\Url
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    private string $imageUrl;

    /**
     * CreateTestDto constructor.
     * @param string|null $question
     * @param string $answer
     * @param array|null $hints
     * @param string $imageUrl
     */
    public function __construct(?string $question, string $answer, ?array $hints, string $imageUrl)
    {
        $this->question = $question;
        $this->answer = $answer;
        $this->hints = $hints;
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return string|null
     */
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }

    /**
     * @return array|null
     */
    public function getHints(): ?array
    {
        return $this->hints;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }
}
