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
use Crtl\RequestDTOResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDTOResolverBundle\Attribute\QueryParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;
use Crtl\RequestDTOResolverBundle\Attribute\RouteParam;

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
