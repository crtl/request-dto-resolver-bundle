<?php

/*
 * This file is part of a private project.
 *
 * Copyright 2026 Crtl
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Crtl\RequestDtoResolverBundle\Test\Unit\Attribute;

use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class FileParamTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetValueFromRequestReturnsUploadedFileFromRequestFilesBag(): void
    {
        $paramName = 'test_file';
        $uploadedFile = $this->createMock(UploadedFile::class);

        $request = new Request(files: [$paramName => $uploadedFile]);

        $fileParam = new FileParam($paramName);

        $this->assertSame($uploadedFile, $fileParam->getValueFromRequest($request));
    }

    public function testGetValueFromRequestReturnsNullIfFileIsMissing(): void
    {
        $paramName = 'missing_file';

        $request = new Request();

        $fileParam = new FileParam($paramName);

        $this->assertNull($fileParam->getValueFromRequest($request));
    }

    public function testHasValueInRequestReturnsWhetherValueIsExistsInRequest(): void
    {
        $paramName = 'test_file';
        $uploadedFile = $this->createMock(UploadedFile::class);

        $request = new Request(files: [$paramName => $uploadedFile]);

        $fileParam = new FileParam($paramName);

        $this->assertTrue($fileParam->hasValueInRequest($request));
        $this->assertFalse($fileParam->hasValueInRequest(new Request()));
    }
}
