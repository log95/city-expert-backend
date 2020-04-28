<?php

namespace App\Service\File;

use Symfony\Component\HttpFoundation\Request;

interface FileUploaderInterface
{
    public function upload(Request $request): string;
}
