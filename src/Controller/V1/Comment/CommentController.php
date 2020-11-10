<?php

namespace App\Controller\V1\Comment;

use App\Dto\CreateCommentDto;
use App\Entity\Test;
use App\Entity\TestComment;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Route("/tests/{test}/comments", name="test.comments.")
 */
class CommentController extends AbstractFOSRestController
{
    /**
     * @Get("/", name="list")
     * @param Test $test
     * @return View
     */
    public function index(Test $test): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $isCreator = $user->getId() === $test->getCreatedBy()->getId();
        $isModerator = $user->getId() === $test->getModerator()->getId();

        if (!$isCreator && !$isModerator) {
            throw new AccessDeniedHttpException('NOT_CREATOR_OR_MODERATOR');
        }

        $commentsRepository = $this->getDoctrine()->getRepository(TestComment::class);

        $messages = $commentsRepository->getMessages($test);

        return $this->view($messages, Response::HTTP_OK);
    }

    /**
     * @Post("/", name="save")
     * @ParamConverter("createCommentDto", converter="fos_rest.request_body")
     * @param Test $test
     * @param CreateCommentDto $createCommentDto
     * @param ConstraintViolationListInterface $validationErrors
     * @return View
     */
    public function save(
        Test $test,
        CreateCommentDto $createCommentDto,
        ConstraintViolationListInterface $validationErrors
    ): View {
        /** @var User $user */
        $user = $this->getUser();

        $isCreator = $user->getId() === $test->getCreatedBy()->getId();
        $isModerator = $user->getId() === $test->getModerator()->getId();

        if (!$isCreator && !$isModerator) {
            throw new AccessDeniedHttpException('NOT_CREATOR_OR_MODERATOR');
        }

        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $comment = new TestComment($test, $user, $createCommentDto->getMessage());

        $em = $this->getDoctrine()->getManager();

        $em->persist($comment);
        $em->flush();

        $result = [
            'id' => $comment->getId(),
            'message' => $comment->getMessage(),
            'author_name' => $comment->getCreatedBy()->getName(),
            'created_at' => $comment->getCreatedAt(),
        ];

        return $this->view($result, Response::HTTP_CREATED);
    }
}
