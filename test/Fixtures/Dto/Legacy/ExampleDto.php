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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\Legacy;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\Nested\NestedChildMixedDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
class ExampleDto
{
    // Matches someParam in request body
    #[BodyParam, Assert\NotBlank]
    public mixed $someParam;

    // Matches file in uploaded files
    #[FileParam, Assert\NotNull]
    public mixed $file;

    // Matches Content-Type header in headers
    #[HeaderParam('Content-Type'), Assert\NotBlank]
    public mixed $contentType;

    // Pass string to param if property does not match param name.
    // Matches queryParamName in query params
    #[QueryParam('queryParamName'), Assert\NotBlank]
    public mixed $query;

    // Matches id
    /**
     * @var int
     */
    #[RouteParam, Assert\NotBlank]
    public mixed $id;

    // Nested DTOs are supported for BodyParam and QueryParam
    #[BodyParam('nested'), Assert\Valid]
    public ?NestedChildMixedDto $nestedBodyDto;

    #[QueryParam('nested')]
    public ?NestedChildMixedDto $nestedQueryParamDto;

    // Optionally implement constructor which accepts request object
    public function __construct(public readonly Request $request)
    {
    }
}
