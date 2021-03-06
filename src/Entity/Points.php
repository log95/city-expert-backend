<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PointsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Points
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Test")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $test;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PointsType")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $points;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TestHint")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $hint;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

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

    public function getType(): ?PointsType
    {
        return $this->type;
    }

    public function setType(?PointsType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

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
}
