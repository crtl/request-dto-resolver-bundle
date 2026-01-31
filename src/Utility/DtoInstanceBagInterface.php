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

interface DtoInstanceBagInterface
{
    /**
     * Name of the request attribute that stores DTO instances.
     *
     * @internal
     */
    public const DTO_INSTANCES_ATTRIBUTE_KEY = '_request_dto_instances';

    /**
     * @return array<class-string, object>
     */
    public function getRegisteredInstances(Request $request): array;

    public function registerInstance(object $instance, Request $request): void;

    /**
     * @param class-string $className
     */
    public function hasRegisteredInstance(string $className, Request $request): bool;

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function getRegisteredInstance(string $className, Request $request): ?object;
}
