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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\New;

use Crtl\RequestDtoResolverBundle\Attribute\QueryParam;

class TransformQueryParamRequestDto
{
    #[QueryParam(transformType: 'int')]
    public int $queryInt;

    #[QueryParam(transformType: 'int')]
    public ?int $queryNullableInt;

    #[QueryParam(transformType: 'float')]
    public float $queryFloat;

    #[QueryParam(transformType: 'float')]
    public ?float $queryNullableFloat;

    #[QueryParam(transformType: 'bool')]
    public bool $queryBool;

    #[QueryParam(transformType: 'bool')]
    public ?bool $queryNullableBool;
}
