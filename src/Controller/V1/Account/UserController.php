<?php

declare(strict_types=1);

namespace App\Controller\V1\Account;

use App\Entity\User;
use App\Repository\PointsRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/account/user", name="account.user.")
 */
class UserController extends AbstractFOSRestController
{
    /**
     * @Get("/", name="show")
     */
    public function show(): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $result = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];

        return $this->view($result, Response::HTTP_OK);
    }

    /**
     * @Get("/points/", name="points")
     * @param PointsRepository $pointsRepository
     * @return View
     */
    public function getTotalPoints(PointsRepository $pointsRepository): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $points = $pointsRepository->getUserTotalPoints($user);

        return $this->view($points, Response::HTTP_OK);
    }
}
