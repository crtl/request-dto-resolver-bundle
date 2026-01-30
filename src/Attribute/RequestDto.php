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

namespace Crtl\RequestDTOResolverBundle\Attribute;

use Crtl\RequestDTOResolverBundle\RequestDtoResolver;

/**
 * Marks a class as request DTO which will be resolved and validated
 * by {@link RequestDtoResolver} for controller action arguments.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class RequestDto
{
}
