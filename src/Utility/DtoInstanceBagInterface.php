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

namespace Crtl\RequestDtoResolverBundle\Utility;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @codeCoverageIgnore
 */
interface DtoInstanceBagInterface
{
    /**
     * Name of the request attribute that stores DTO instances.
     *
     * @internal
     */
    public const DTO_INSTANCES_ATTRIBUTE_KEY = '_request_dto_instances';

    /**
     * Name of the request attribute that stores hydration violations.
     *
     * @internal
     */
    public const DTO_HYDRATION_VIOLATIONS_KEY = '_request_dto_hydration_violations';

    /**
     * @return array<class-string, object>
     */
    public function getRegisteredInstances(Request $request): array;

    public function registerInstance(object $instance, Request $request): void;

    /**
     * Register constraint violation that occured during hydration of DTO.
     *
     * These constraints are normally violated when using strict typed DTOs
     * with mismatching request data.
     *
     * @param class-string $className
     */
    public function registerHydrationViolations(
        string $className,
        ConstraintViolationListInterface $violations,
        Request $request
    ): void;

    /**
     * @param class-string $className
     */
    public function getHydrationViolations(string $className, Request $request): ?ConstraintViolationListInterface;

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
