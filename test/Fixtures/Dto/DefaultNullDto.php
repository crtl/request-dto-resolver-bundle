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
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;

#[RequestDto(defaultNull: true)]
class DefaultNullDto
{
    #[BodyParam]
    public ?string $nullableString;

    #[BodyParam]
    public ?int $nullableInt;

    #[BodyParam]
    public string $nonNullableString;

    #[BodyParam]
    public int $nonNullableInt;
}
