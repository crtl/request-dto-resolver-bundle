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
 * Dto containing nested child dtos as single child and array of children.
 */
#[RequestDto]
class DtoWithChildDto
{
    use AllTypesBodyParamsTrait;

    #[BodyParam]
    public ?NestedChildDTO $child;

    /**
     * @var NestedChildDTO[]
     */
    #[BodyParam]
    public array $children;
}
