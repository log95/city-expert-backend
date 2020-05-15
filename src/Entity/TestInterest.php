<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TestInterestRepository")
 * @ORM\Table(name="test_interest", uniqueConstraints={@ORM\UniqueConstraint(name="unique_interest", columns={"user_id", "test_id"})})
 */
class TestInterest
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="testInterests")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Test", inversedBy="interests")
     * @ORM\JoinColumn(name="test_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $test;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLiked;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsLiked(): ?bool
    {
        return $this->isLiked;
    }

    public function setIsLiked(bool $isLiked): self
    {
        $this->isLiked = $isLiked;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTest(): ?Test
    {
        return $this->test;
    }

    public function setTest(?Test $test): self
    {
        $this->test = $test;

        return $this;
    }
}
