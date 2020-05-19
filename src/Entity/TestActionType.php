<?php

namespace App\Entity;

use App\Repository\TestActionTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TestActionTypeRepository::class)
 */
class TestActionType
{
    const CORRECT_ANSWER = 'correct_answer';
    const SHOW_ANSWER = 'show_answer';
    const SHOW_HINT = 'show_hint';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
