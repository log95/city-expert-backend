<?php

namespace App\Controller\V1\Chat;

use App\Dto\CreateMessageDto;
use App\Entity\Chat;
use App\Entity\ChatMessage;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Route("/chat/{chat}/messages", name="chat.message.")
 */
class MessageController extends AbstractFOSRestController
{
    /**
     * @Get("/", name="list")
     */
    public function index(Chat $chat)
    {
        /** @var User $user */
        $user = $this->getUser();

        $test = $chat->getTest();

        $isCreator = $user->getId() === $test->getCreatedBy()->getId();
        $isModerator = $user->getId() === $test->getModerator()->getId();

        if (!$isCreator && !$isModerator) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }

        $messageRepository = $this->getDoctrine()->getRepository(ChatMessage::class);

        $messages = $messageRepository->getMessages($chat);

        return $this->view($messages, Response::HTTP_OK);
    }

    /**
     * @Post("/", name="save")
     *
     * @ParamConverter("createMessageDto", converter="fos_rest.request_body")
     */
    public function save(
        Chat $chat,
        CreateMessageDto $createMessageDto,
        ConstraintViolationListInterface $validationErrors
    ) {
        /** @var User $user */
        $user = $this->getUser();

        $test = $chat->getTest();

        $isCreator = $user->getId() === $test->getCreatedBy()->getId();
        $isModerator = $user->getId() === $test->getModerator()->getId();

        if (!$isCreator && !$isModerator) {
            return $this->view(null, Response::HTTP_FORBIDDEN);
        }

        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $message = new ChatMessage($chat, $user, $createMessageDto->getMessage());

        $em = $this->getDoctrine()->getManager();

        $em->persist($message);
        $em->flush();

        return $this->view(['id' => $message->getId()], Response::HTTP_CREATED);
    }
}