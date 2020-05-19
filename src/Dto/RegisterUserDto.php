<?php

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\NotVerifiedEmail;

class RegisterUserDto
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(max = 180)
     * @Assert\Email
     * @NotVerifiedEmail
     * @Serializer\Type("string")
     */
    private string $email;

    /**
     * TODO: add complex rule
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Serializer\Type("string")
     */
    private string $password;

    /**
     * RegisterUserDto constructor.
     * @param string $email
     * @param string $password
     */
    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
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
