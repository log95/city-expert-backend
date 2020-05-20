<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PointsTypeRepository")
 */
class PointsType
{
    const CORRECT_ANSWER = 'correct_answer';
    const SHOW_ANSWER = 'show_answer';
    const SHOW_HINT = 'show_hint';
    const REGISTRATION = 'registration';

    const POINTS_MAP = [
        PointsType::CORRECT_ANSWER => 10,
        PointsType::SHOW_ANSWER => -5,
        PointsType::SHOW_HINT => -1,
        PointsType::REGISTRATION => 100,
    ];

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

    public function getPoints(): int
    {
        return self::POINTS_MAP[$this->name];
    }
}
