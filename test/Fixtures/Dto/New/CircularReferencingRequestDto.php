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

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;

#[RequestDto]
class CircularReferencingRequestDto
{
    #[BodyParam]
    public ?CircularReferencingRequestDto $prop;

    /**
     * @var CircularReferencingRequestDto[]
     */
    #[BodyParam]
    public array $array;
}
