<?php

namespace App\Entity;

use App\Repository\TestActionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TestActionRepository::class)
 * @ORM\HasLifecycleCallbacks
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
    private $type;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    public function __construct(User $user, Test $test, TestActionType $type, ?TestHint $hint = null)
    {
        $this->user = $user;
        $this->test = $test;
        $this->type = $type;
        $this->hint = $hint;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
    }

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

    public function getType(): TestActionType
    {
        return $this->type;
    }

    public function setType(TestActionType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
