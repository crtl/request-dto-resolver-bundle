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

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[RequestDto]
final class AllParamTypesDTO
{
    #[BodyParam]
    public ?string $body;

    #[QueryParam]
    public ?string $query;

    #[HeaderParam('User-Agent')]
    public ?string $userAgent;

    #[RouteParam('_route')]
    public ?string $routeName;

    #[FileParam]
    public ?UploadedFile $file;
}
