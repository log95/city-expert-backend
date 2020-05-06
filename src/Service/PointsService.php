<?php

namespace App\Service;

use App\Entity\Points;
use App\Entity\PointsType;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

// TODO: возможно нужно перенести в repository
class PointsService
{
    private const POINTS_MAP = [
        PointsType::CORRECT_ANSWER => 10,
        PointsType::WRONG_ANSWER => -1,
        PointsType::SHOW_ANSWER => -5,
        PointsType::HINT => -1,
        PointsType::REGISTRATION => 10,
    ];

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addForRegistration(User $user): int
    {
        $points = self::POINTS_MAP[PointsType::REGISTRATION];

        $this->add($user, PointsType::REGISTRATION, $points);

        return $points;
    }

    public function addForCorrectAnswer(User $user, Test $test): int
    {
        $points = self::POINTS_MAP[PointsType::CORRECT_ANSWER];

        $this->add($user, PointsType::CORRECT_ANSWER, $points, $test);

        return $points;
    }

    public function reduceForWrongAnswer(User $user, Test $test): int
    {
        $points = self::POINTS_MAP[PointsType::WRONG_ANSWER];

        $this->add($user, PointsType::WRONG_ANSWER, $points, $test);

        return $points;
    }

    public function reduceForShowAnswer(User $user, Test $test): int
    {
        $points = self::POINTS_MAP[PointsType::SHOW_ANSWER];

        $this->add($user, PointsType::SHOW_ANSWER, $points, $test);

        return $points;
    }

    public function reduceForHint(User $user, TestHint $hint): int
    {
        $points = self::POINTS_MAP[PointsType::HINT];

        $this->add($user, PointsType::HINT, $points, null, $hint);

        return $points;
    }

    private function add(User $user, string $pointsTypeName, int $points, ?Test $test = null, ?TestHint $hint = null): void
    {
        $pointsTypeRepository = $this->em->getRepository(PointsType::class);
        /** @var PointsType $pointsType */
        $pointsType = $pointsTypeRepository->findOneBy(['name' => $pointsTypeName]);

        $pointsEntity = new Points();
        $pointsEntity->setUser($user);
        $pointsEntity->setType($pointsType);
        $pointsEntity->setPoints($points);
        $pointsEntity->setTest($test);
        $pointsEntity->setHint($hint);

        $this->em->persist($pointsEntity);
        $this->em->flush();
    }
}