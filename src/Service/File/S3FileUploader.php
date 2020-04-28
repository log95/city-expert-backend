<?php

namespace App\Service\File;

use Aws\S3\S3Client;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Request;

class S3FileUploader implements FileUploaderInterface
{
    private FileUploader $baseFileUploader;
    private S3Client $s3Client;
    private string $bucket;

    /**
     * TODO: сделать по нормальному или как-то описать, что getAdapter есть только у Filesystem
     * Можно попробовать по autowire получить s3Client и самому сформировать Filesystem
     *
     * S3FileUploader constructor.
     * @param Filesystem $s3Storage
     */
    public function __construct(FilesystemInterface $s3Storage)
    {
        if (!($s3Storage instanceof Filesystem)) {
            throw new \RuntimeException('S3FileUploader expects Filesystem');
        }

        $this->baseFileUploader = new FileUploader($s3Storage);
        $this->s3Client = $s3Storage->getAdapter()->getClient();
        $this->bucket = $s3Storage->getAdapter()->getBucket();
    }

    public function upload(Request $request): string
    {
        $filePath = $this->baseFileUploader->upload($request);

        return $this->s3Client->getObjectUrl($this->bucket, $filePath);
    }
}
