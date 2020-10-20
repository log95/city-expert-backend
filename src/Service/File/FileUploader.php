<?php

namespace App\Service\File;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;

class FileUploader implements FileUploaderInterface
{
    private const MIME_TYPES_TO_FILE_EXTENSIONS_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    private const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024;

    private FilesystemInterface $storage;

    public function __construct(FilesystemInterface $storage)
    {
        $this->storage = $storage;
    }

    // TODO: проверить случай, если пишут правильный mime тип, но грузят pdf
    public function upload(Request $request): string
    {
        $this->checkRestrictions($request);

        $newFilePath = $this->getNewFileName($request);

        $isSuccess = $this->storage->write($newFilePath, $request->getContent());
        if (!$isSuccess) {
            throw new \RuntimeException('Can not upload file');
        }

        return $newFilePath;
    }

    private function checkRestrictions(Request $request): void
    {
        $this->checkFileSize($request);
        $this->checkFileExtension($request);
    }

    private function checkFileSize(Request $request): void
    {
        $contentLength = $request->headers->get('Content-Length');
        if (!$contentLength) {
            throw new FileException('Content length is not set');
        }

        if ($contentLength > self::MAX_FILE_SIZE_BYTES) {
            throw new FileException('File is too big.');
        }
    }

    private function checkFileExtension(Request $request): void
    {
        if (!$request->headers->has('Content-Type')) {
            throw new FileException('Mime type of file is not set.');
        }

        $fileMimeType = $request->headers->get('Content-Type');
        $allowedMimeTypes = array_keys(self::MIME_TYPES_TO_FILE_EXTENSIONS_MAP);

        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            throw new FileException('Unsupported mime type.');
        }
    }

    private function getNewFileName(Request $request): string
    {
        $fileMimeType = $request->headers->get('Content-Type');
        $fileExtension = self::MIME_TYPES_TO_FILE_EXTENSIONS_MAP[$fileMimeType];

        return uniqid('', true) . '.' . $fileExtension;
    }
}
