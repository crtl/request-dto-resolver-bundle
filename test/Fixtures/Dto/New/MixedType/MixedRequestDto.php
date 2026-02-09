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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\MixedType;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[RequestDto]
class MixedRequestDto
{
    /**
     * @var string
     */
    #[BodyParam]
    public mixed $bodyString;

    /**
     * @var string|null
     */
    #[BodyParam]
    public mixed $bodyNullableString;

    /**
     * @var int
     */
    #[BodyParam]
    public mixed $bodyInt;

    /**
     * @var int|null
     */
    #[BodyParam]
    public mixed $bodyNullableInt;

    /**
     * @var float
     */
    #[BodyParam]
    public mixed $bodyFloat;

    /**
     * @var float|null
     */
    #[BodyParam]
    public mixed $bodyNullableFloat;

    /**
     * @var bool
     */
    #[BodyParam]
    public mixed $bodyBool;

    /**
     * @var bool|null
     */
    #[BodyParam]
    public mixed $bodyNullableBool;

    /**
     * @var mixed[]
     */
    #[BodyParam]
    public mixed $bodyArray;

    /**
     * @var mixed[]|null
     */
    #[BodyParam]
    public mixed $bodyNullableArray;

    // Query params - all scalar types + array
    /**
     * @var string
     */
    #[QueryParam]
    public mixed $queryString;

    /**
     * @var string|null
     */
    #[QueryParam]
    public mixed $queryNullableString;

    /**
     * @var int
     */
    #[QueryParam(transformType: 'int')]
    public mixed $queryInt;

    /**
     * @var int|null
     */
    #[QueryParam(transformType: 'int')]
    public mixed $queryNullableInt;

    /**
     * @var float
     */
    #[QueryParam(transformType: 'float')]
    public mixed $queryFloat;

    /**
     * @var float|null
     */
    #[QueryParam(transformType: 'float')]
    public mixed $queryNullableFloat;

    /**
     * @var bool
     */
    #[QueryParam(transformType: 'bool')]
    public mixed $queryBool;

    /**
     * @var bool|null
     */
    #[QueryParam(transformType: 'bool')]
    public mixed $queryNullableBool;

    /**
     * @var string[]
     */
    #[QueryParam]
    public mixed $queryArray;

    /**
     * @var string[]|null
     */
    #[QueryParam]
    public mixed $queryNullableArray;

    // Body params with custom names
    /**
     * @var string
     */
    #[BodyParam('body_custom_name')]
    public mixed $bodyCustomName;

    // Query params with custom names
    /**
     * @var string
     */
    #[QueryParam('query_custom_name')]
    public mixed $queryCustomName;

    /**
     * @var int
     */
    #[QueryParam(name: 'user_id', transformType: 'int')]
    public mixed $userId;

    // Header params
    /**
     * @var string
     */
    #[HeaderParam('X-Custom-Header')]
    public mixed $headerString;

    /**
     * @var string|null
     */
    #[HeaderParam('X-Nullable-Header')]
    public mixed $headerNullableString;

    /**
     * @var string
     */
    #[HeaderParam('X-Request-ID')]
    public mixed $requestId;

    // Route params
    /**
     * @var string
     */
    #[RouteParam('id')]
    public mixed $routeId;

    /**
     * @var string|null
     */
    #[RouteParam('slug')]
    public mixed $routeSlug;

    /**
     * @var string
     */
    #[RouteParam('page_number')]
    public mixed $pageNumber;

    // File params
    /**
     * @var UploadedFile|null
     */
    #[FileParam]
    public mixed $file;

    /**
     * @var UploadedFile|null
     */
    #[FileParam('uploaded_document')]
    public mixed $uploadedDocument;
}
