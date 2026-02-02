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

namespace Crtl\RequestDtoResolverBundle\Attribute;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDto} from {@link Request::$headers}.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class HeaderParam extends AbstractParam
{
    public function getValueFromRequest(Request $request): ?string
    {
        return $request->headers->get($this->getName());
    }
}
