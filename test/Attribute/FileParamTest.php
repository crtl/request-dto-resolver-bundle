<?php

namespace Crtl\RequestDTOResolverBundle\Test\Attribute;

use Crtl\RequestDTOResolverBundle\Attribute\FileParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileParamTest extends TestCase
{
    public function testGetValueFromRequest()
    {
        $paramName = "test_file";
        $uploadedFile = $this->createMock(UploadedFile::class);

        $request = new Request([], [], [], [], [$paramName => $uploadedFile]);

        $fileParam = new FileParam($paramName);

        $this->assertSame($uploadedFile, $fileParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestWithMissingFile()
    {
        $paramName = "missing_file";

        $request = new Request();

        $fileParam = new FileParam($paramName);

        $this->assertNull($fileParam->getValueFromRequest($request));
    }
}
