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
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Crtl\RequestDtoResolverBundle\Test\Fixtures\Dto\AllTypesBodyParamsTrait;

#[RequestDto]
class RequestDtoWithShallowNesting
{
    use AllTypesBodyParamsTrait;

    #[BodyParam]
    public ?ChildRequestDto $child;

    /**
     * @var ChildRequestDto[]
     */
    #[BodyParam]
    public array $children;
}
