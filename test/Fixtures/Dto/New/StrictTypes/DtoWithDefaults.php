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
use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;

#[RequestDto]
class DtoWithDefaults
{
    #[BodyParam]
    public string $bodyStringWithDefault = 'default-body-string';

    #[BodyParam]
    public int $bodyIntWithDefault = 42;

    #[BodyParam]
    public float $bodyFloatWithDefault = 3.14;

    #[BodyParam]
    public bool $bodyBoolWithDefault = true;

    /** @var string[] */
    #[BodyParam]
    public array $bodyArrayWithDefault = ['default', 'values'];

    #[QueryParam]
    public string $queryStringWithDefault = 'default-query-string';

    #[QueryParam(transformType: 'int')]
    public int $queryIntWithDefault = 99;

    #[QueryParam(transformType: 'bool')]
    public bool $queryBoolWithDefault = false;
}
