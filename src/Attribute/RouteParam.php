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
 * Attribute to resolve value for property of {@link RequestDto} from requests route params.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class RouteParam extends AbstractParam
{
    public function hasValueInRequest(Request $request): bool
    {
        $key = '_route_params';

        return $request->attributes->has($key)
            && array_key_exists(
                $this->getName(),
                $request->attributes->get($key, []),
            );
    }

    public function getValueFromRequest(Request $request): mixed
    {
        /** @var array<string, mixed> $routeParams */
        $routeParams = $request->attributes->get('_route_params') ?? [];

        return $routeParams[$this->getName()] ?? null;
    }
}
