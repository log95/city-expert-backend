<?php

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\NotVerifiedEmail;

class RegisterUserDto
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(max = 100)
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    private string $name;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max = 180)
     * @Assert\Email
     * @NotVerifiedEmail
     * @Serializer\Type("string")
     */
    private string $email;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min = 8, max = 100)
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    private string $password;

    public function __construct(string $name, string $email, string $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
