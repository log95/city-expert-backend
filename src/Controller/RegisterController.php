<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegisterType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use FOS\RestBundle\Controller\Annotations\Post;

class RegisterController extends AbstractFOSRestController
{
    /**
     * @Post("/register/", name="register")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \FOS\RestBundle\View\View
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $form = $this->createForm(UserRegisterType::class, $user);

        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $password = $data['password'];

            $user->setPassword($encoder->encodePassword($user, $password));

            $em->persist($user);
            $em->flush();

            return $this->view(null, Response::HTTP_CREATED);
        } else {
            return $this->view($form->getErrors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}