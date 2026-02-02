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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Legacy;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;

#[RequestDto]
final class ParameterMappingDTO
{
    #[BodyParam('different_body_name')]
    public $bodyParam;

    #[QueryParam('different_query_name')]
    public $queryParam;

    #[HeaderParam('X-Custom-Header')]
    public $headerParam;

    #[RouteParam('id')]
    public $routeParam;
}
