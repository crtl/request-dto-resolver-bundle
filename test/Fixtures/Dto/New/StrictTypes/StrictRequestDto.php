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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New\StrictTypes;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\FileParam;
use Crtl\RequestDtoResolverBundle\Attribute\HeaderParam;
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Attribute\RouteParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[RequestDto]
class StrictRequestDto
{
    // Body params - all scalar types + array
    #[BodyParam]
    public string $bodyString;

    #[BodyParam]
    public ?string $bodyNullableString;

    #[BodyParam]
    public int $bodyInt;

    #[BodyParam]
    public ?int $bodyNullableInt;

    #[BodyParam]
    public float $bodyFloat;

    #[BodyParam]
    public ?float $bodyNullableFloat;

    #[BodyParam]
    public bool $bodyBool;

    #[BodyParam]
    public ?bool $bodyNullableBool;

    /** @var mixed[] */
    #[BodyParam]
    public array $bodyArray;

    /** @var mixed[]|null */
    #[BodyParam]
    public ?array $bodyNullableArray;

    // Query params - all scalar types + array
    #[QueryParam]
    public string $queryString;

    #[QueryParam]
    public ?string $queryNullableString;

    /** @var string[] */
    #[QueryParam]
    public array $queryArray;

    /** @var string[]|null */
    #[QueryParam]
    public ?array $queryNullableArray;

    // Body params with custom names
    #[BodyParam('body_custom_name')]
    public string $bodyCustomName;

    // Query params with custom names
    #[QueryParam('query_custom_name')]
    public string $queryCustomName;

    #[QueryParam(name: 'user_id', transformType: 'int')]
    public int $userId;

    // Header params
    #[HeaderParam('X-Custom-Header')]
    public string $headerString;

    #[HeaderParam('X-Nullable-Header')]
    public ?string $headerNullableString;

    #[HeaderParam('X-Request-ID')]
    public string $requestId;

    // Route params
    #[RouteParam('id')]
    public string $routeId;

    #[RouteParam('slug')]
    public ?string $routeSlug;

    #[RouteParam('page_number')]
    public string $pageNumber;

    // File params
    #[FileParam]
    public ?UploadedFile $file;

    #[FileParam('uploaded_document')]
    public ?UploadedFile $uploadedDocument;
}
