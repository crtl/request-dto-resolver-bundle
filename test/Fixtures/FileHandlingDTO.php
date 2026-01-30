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

namespace Crtl\RequestDTOResolverBundle\Test\Fixtures;

use Crtl\RequestDTOResolverBundle\Attribute\FileParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
class FileHandlingDTO
{
    #[FileParam]
    #[Assert\NotNull]
    public ?UploadedFile $file;

    #[FileParam('optional_file')]
    public ?UploadedFile $optionalFile = null;
}
