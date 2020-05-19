<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TestRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Test
{
    const STATUS_IN_PROCESSING = 'in_processing';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $question;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $answer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TestHint", mappedBy="test")
     */
    private $hints = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $imageUrl;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\City", inversedBy="tests")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TestInterest", mappedBy="test")
     */
    private $interests;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Points", mappedBy="test")
     */
    private $points;

    /**
     * @ORM\Column(type="string", length=50, options={"default": "draft"})
     */
    private $currentStatus;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tests")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $moderator;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->interests = new ArrayCollection();
        $this->hints = new ArrayCollection();
        $this->points = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
    }

    public function getCurrentStatus()
    {
        return $this->currentStatus;
    }

    public function setCurrentStatus($currentStatus, $context = [])
    {
        $this->currentStatus = $currentStatus;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function isRightAnswer(string $answer): bool
    {
        return $this->getAnswer() === trim($answer);
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection|TestInterest[]
     */
    public function getInterests(): Collection
    {
        return $this->interests;
    }

    public function addInterest(TestInterest $interest): self
    {
        if (!$this->interests->contains($interest)) {
            $this->interests[] = $interest;
            $interest->setTest($this);
        }

        return $this;
    }

    public function removeInterest(TestInterest $interest): self
    {
        if ($this->interests->contains($interest)) {
            $this->interests->removeElement($interest);
            // set the owning side to null (unless already changed)
            if ($interest->getTest() === $this) {
                $interest->setTest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TestHint[]
     */
    public function getHints(): Collection
    {
        return $this->hints;
    }

    public function addHint(TestHint $hint): self
    {
        if (!$this->hints->contains($hint)) {
            $this->hints[] = $hint;
            $hint->setTest($this);
        }

        return $this;
    }

    public function removeHint(TestHint $hint): self
    {
        if ($this->hints->contains($hint)) {
            $this->hints->removeElement($hint);
            // set the owning side to null (unless already changed)
            if ($hint->getTest() === $this) {
                $hint->setTest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Points[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Points $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setTest($this);
        }

        return $this;
    }

    public function removePoint(Points $point): self
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            // set the owning side to null (unless already changed)
            if ($point->getTest() === $this) {
                $point->setTest(null);
            }
        }

        return $this;
    }

    public function getModerator(): ?User
    {
        return $this->moderator;
    }

    public function setModerator(?User $moderator): self
    {
        $this->moderator = $moderator;

        return $this;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
