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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;

/**
 * Dto with strict typed properties but not equivalent validation attributes to ensure data is correct.
 */
#[RequestDto]
class TypeConflictingDto
{
    #[BodyParam]
    public int $intProperty;

    #[BodyParam]
    public int $floatProperty;

    #[BodyParam]
    public string $stringProperty;

    #[BodyParam]
    public bool $boolProperty;

    #[BodyParam]
    public array $arrayProperty; // @phpstan-ignore-line
}
