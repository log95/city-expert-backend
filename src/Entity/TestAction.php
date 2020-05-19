<?php

namespace App\Entity;

use App\Repository\TestActionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TestActionRepository::class)
 */
class TestAction
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Test::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $test;

    /**
     * @ORM\ManyToOne(targetEntity=TestHint::class)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $hint;

    /**
     * @ORM\ManyToOne(targetEntity=TestActionType::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $actionType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTest(): Test
    {
        return $this->test;
    }

    public function setTest(Test $test): self
    {
        $this->test = $test;

        return $this;
    }

    public function getHint(): ?TestHint
    {
        return $this->hint;
    }

    public function setHint(?TestHint $hint): self
    {
        $this->hint = $hint;

        return $this;
    }

    public function getActionType(): TestActionType
    {
        return $this->actionType;
    }

    public function setActionType(TestActionType $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }
}
