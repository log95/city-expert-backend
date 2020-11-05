<?php

namespace App\Controller\V1\Publication;

use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/publication", name="publication.")
 */
class TestController extends AbstractFOSRestController
{
    /**
     * Test info for publication.
     *
     * @Get("/tests/{test}/", name="test.show")
     * @param Test $test
     * @return View
     */
    public function show(Test $test): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $isCreator = $user->getId() === $test->getCreatedBy()->getId();
        $isModerator = $user->getId() === $test->getModerator()->getId();

        if (!$isCreator && !$isModerator) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }

        $hints = $test->getHints()->map(function (TestHint $hint) {
            return [
                'id' => $hint->getId(),
                'text' => $hint->getText(),
            ];
        });

        $result = [
            'id' => $test->getId(),
            'city' => [
                'id' => $test->getCity()->getId(),
                'name' => $test->getCity()->getName(),
            ],
            'question' => $test->getQuestion(),
            'image_url' =>  $test->getImageUrl(),
            'answer' => $test->getAnswer(),
            'hints' => $hints,
            'status' => $test->getCurrentStatus(),
            'chat_id' => $test->getChat()->getId(),
        ];

        return $this->view($result, Response::HTTP_OK);
    }
}
