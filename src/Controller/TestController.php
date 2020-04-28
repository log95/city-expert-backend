<?php

namespace App\Controller;

use App\Entity\Test;
use App\Form\CreateTestType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Put;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractFOSRestController
{
    /**
     * @Post("/tests/", name="test.save")
     */
    public function save(Request $request)
    {
        $res = $defaultStorage->has('index2.png');
        print_r($res);
        return $this->view(null, Response::HTTP_CREATED);

        $test = new Test();

        $form = $this->createForm(CreateTestType::class, $test);

        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        $fileContent = base64_decode($data['image']);

        return $this->view($fileContent, Response::HTTP_CREATED);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();


            $em->persist($test);
            $em->flush();

            return $this->view(null, Response::HTTP_CREATED);
        } else {
            return $this->view($form->getErrors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @Get("/tests/{test}/", name="test.show")
     */
    public function show(Test $test)
    {
        return $this->view($test);
    }

    /**
     * @Put("/tests/{test}/", name="test.update")
     */
    public function update(Test $test)
    {

    }

    /**
     * @Delete("/tests/{test}/", name="test.delete")
     */
    public function delete(Test $test)
    {

    }
}
