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

namespace Crtl\RequestDTOResolverBundle\Test\Fixtures\Legacy;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\FileParam;
use Crtl\RequestDTOResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDTOResolverBundle\Attribute\QueryParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;
use Crtl\RequestDTOResolverBundle\Attribute\RouteParam;

#[RequestDto]
final class AllParamTypesDTO
{
    #[BodyParam]
    public $body;

    #[QueryParam]
    public $query;

    #[HeaderParam('User-Agent')]
    public $userAgent;

    #[RouteParam('_route')]
    public $routeName;

    #[FileParam]
    public $file;
}
