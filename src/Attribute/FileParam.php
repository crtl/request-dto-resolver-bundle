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

namespace Crtl\RequestDtoResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDto} from {@link Request::$files}.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FileParam extends AbstractParam
{
    public function hasValueInRequest(Request $request): bool
    {
        return $request->files->has($this->getName());
    }

    /**
     * @throws \UnexpectedValueException When FileBag::get() does not return null or `UploadedFile` instance
     */
    public function getValueFromRequest(Request $request): ?UploadedFile
    {
        $file = $request->files->get($this->getName());

        assert(
            is_null($file) || $file instanceof UploadedFile,
            sprintf(
                'Expected %s to return an instance of %s but got %s instead.',
                get_class($request->files),
                UploadedFile::class,
                get_debug_type($file),
            ),
        );

        return $file;
    }
}
