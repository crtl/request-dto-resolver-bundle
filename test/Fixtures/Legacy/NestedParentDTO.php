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

namespace Crtl\RequestDtoResolverBundle\Test\Fixtures\Legacy;

use Crtl\RequestDtoResolverBundle\Attribute\BodyParam;
use Crtl\RequestDtoResolverBundle\Attribute\RequestDto;
use Symfony\Component\Validator\Constraints as Assert;

#[RequestDto]
final class NestedParentDTO
{
    #[BodyParam]
    #[Assert\NotBlank]
    public $parentName;

    #[BodyParam('child')]
    public ?NestedChildDTO $child;

    /** @var NestedChildDTO[]|null */
    #[BodyParam('children')]
    public $children;

    /** @var array<string, mixed>|null */
    #[BodyParam('child_map')]
    public $childMap;
}
