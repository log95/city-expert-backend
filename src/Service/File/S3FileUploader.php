<?php

namespace App\Service\File;

use Aws\S3\S3Client;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class S3FileUploader implements FileUploaderInterface
{
    private FileUploader $baseFileUploader;
    private S3Client $s3Client;
    private string $bucket;

    private string $environment;

    /**
     * S3FileUploader constructor.
     * @param Filesystem $s3Storage
     */
    public function __construct(FilesystemInterface $s3Storage, KernelInterface $kernel)
    {
        if (!($s3Storage instanceof Filesystem)) {
            throw new \RuntimeException('S3FileUploader expects Filesystem');
        }

        $this->baseFileUploader = new FileUploader($s3Storage);
        $this->s3Client = $s3Storage->getAdapter()->getClient();
        $this->bucket = $s3Storage->getAdapter()->getBucket();

        $this->environment = $kernel->getEnvironment();
    }

    public function upload(Request $request): string
    {
        $filePath = $this->baseFileUploader->upload($request);

        $fileUrl = $this->s3Client->getObjectUrl($this->bucket, $filePath);

        if ($this->environment === 'dev') {
            $fileUrl = str_replace($this->s3Client->getEndpoint(), 'http://localhost:9022/', $fileUrl);
        }

        return $fileUrl;
    }
}
