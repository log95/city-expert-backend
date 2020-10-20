<?php

namespace Tests\Unit;

use App\Service\File\FileUploader;
use App\Tests\UnitTester;
use Codeception\Test\Unit;

class FileUploaderTest extends Unit
{
    protected UnitTester $tester;

    public function testUpload()
    {
        // TODO:
        $url = $this->tester->grabService(FileUploader::class);

        dump(get_class($url));
    }
}
