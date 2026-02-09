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

namespace Crtl\RequestDtoResolverBundle\Factory\Exception;

/**
 * Thrown when a circular reference is detected while hydrating a DTO.
 */
final class CircularReferenceException extends \Exception
{
    /**
     * @param class-string $className The class name where circularity was detected
     * @param string[]     $stack     The hydration stack leading to the circularity
     */
    public function __construct(
        public readonly string $className,
        public readonly array $stack,
    ) {
        parent::__construct(sprintf(
            'Circular reference detected while hydrating DTO "%s". Path: %s -> %s',
            $className,
            implode(' -> ', $stack),
            $className,
        ));
    }
}
