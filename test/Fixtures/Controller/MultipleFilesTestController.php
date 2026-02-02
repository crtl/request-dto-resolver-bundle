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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Controller;

use Crtl\RequestDtoResolverBundle\Test\Fixtures\MultipleFilesDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

final class MultipleFilesTestController
{
    public function __invoke(MultipleFilesDto $dto): JsonResponse
    {
        return new JsonResponse([
            'file_one' => $dto->file_one instanceof UploadedFile ? $dto->file_one->getClientOriginalName() : null,
            'file_two' => $dto->file_two instanceof UploadedFile ? $dto->file_two->getClientOriginalName() : null,
            'file3' => $dto->file3 instanceof UploadedFile ? $dto->file3->getClientOriginalName() : null,
        ]);
    }
}
