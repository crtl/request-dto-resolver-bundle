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

namespace Crtl\RequestDTOResolverBundle\Utility;

use Symfony\Component\HttpFoundation\Request;

final class DtoInstanceBag implements DtoInstanceBagInterface
{
    /**
     * @return array<class-string, object>
     */
    public function getRegisteredInstances(Request $request): array
    {
        /** @var array<class-string, object> $attrs */
        $attrs = $request->attributes->get(self::DTO_INSTANCES_ATTRIBUTE_KEY, []);

        return $attrs;
    }

    public function registerInstance(object $instance, Request $request): void
    {
        // Store instance in request attributes to be validated later on kernel.controller_arguments event
        $request->attributes->set(self::DTO_INSTANCES_ATTRIBUTE_KEY, array_merge(
            $this->getRegisteredInstances($request),
            [
                get_class($instance) => $instance,
            ],
        ));
    }

    /**
     * @param class-string $className
     */
    public function hasRegisteredInstance(string $className, Request $request): bool
    {
        return isset($this->getRegisteredInstances($request)[$className]);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function getRegisteredInstance(string $className, Request $request): ?object
    {
        if ($this->hasRegisteredInstance($className, $request)) {
            /** @var T|null $instance */
            $instance = $this->getRegisteredInstances($request)[$className];

            return $instance;
        }

        return null;
    }
}
