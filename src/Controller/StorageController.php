<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;
use App\Service\File\FileUploaderInterface;
use Symfony\Component\HttpFoundation\Response;

class StorageController extends AbstractFOSRestController
{
    /**
     * @Post("/files/", name="file.save")
     */
    public function store(Request $request, FileUploaderInterface $fileUploader, LoggerInterface $logger)
    {
        try {
            $fileUrl = $fileUploader->upload($request);
        } catch (FileException $e) {
            return $this->view(['Error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            $logger->error($e->getMessage());
            return $this->view(['Error' => 'Server error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->view(['url' => $fileUrl], Response::HTTP_CREATED);
    }
}