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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto;

use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Test dto with mutiple files.
 */
#[RequestDto]
class MultipleFilesDto
{
    #[FileParam, Assert\File]
    public ?UploadedFile $file_one;

    #[FileParam, Assert\File]
    public $file_two; // @phpstan-ignore-line

    /**
     * @var UploadedFile|null
     */
    #[FileParam('file_3'), Assert\File]
    public mixed $file3;
}
