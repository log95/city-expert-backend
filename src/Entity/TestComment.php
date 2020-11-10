<?php

namespace App\Entity;

use App\Repository\TestCommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TestCommentRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class TestComment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Test::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $test;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct(Test $test, User $createdBy, string $message)
    {
        $this->test = $test;
        $this->createdBy = $createdBy;
        $this->message = $message;
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

    public function getTest(): Test
    {
        return $this->test;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
