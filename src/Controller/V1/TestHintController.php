<?php

namespace App\Controller\V1;

use App\Entity\TestHint;
use App\Entity\User;
use App\Service\PointsService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Response;

class TestHintController extends AbstractFOSRestController
{
    /**
     * @Get("/hints/{hint}/", name="hint.show")
     */
    public function showHint(TestHint $hint, PointsService $pointsService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $points = $pointsService->reduceForHint($user, $hint);

        $result = [
            'text' => $hint->getText(),
            'points' => $points,
        ];

        return $this->view($result, Response::HTTP_OK);
    }
}