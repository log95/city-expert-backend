<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PointsTypeRepository")
 */
class PointsType
{
    const CORRECT_ANSWER = 'correct_answer';
    const WRONG_ANSWER = 'wrong_answer';
    const SHOW_ANSWER = 'show_answer';
    const HINT = 'hint';
    const REGISTRATION = 'registration';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
