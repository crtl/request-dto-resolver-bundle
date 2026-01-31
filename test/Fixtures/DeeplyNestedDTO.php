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

namespace Crtl\RequestDTOResolverBundle\Test\Fixtures;

use Crtl\RequestDTOResolverBundle\Attribute\BodyParam;
use Crtl\RequestDTOResolverBundle\Attribute\RequestDto;

#[RequestDto]
final class DeeplyNestedDTO
{
    #[BodyParam('level1')]
    public ?NestedParentDTO $level1;
}
