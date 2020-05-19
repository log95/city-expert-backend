<?php

namespace App\Service\AuthOperation;

class CodeGenerator
{
    public function generateCode(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}