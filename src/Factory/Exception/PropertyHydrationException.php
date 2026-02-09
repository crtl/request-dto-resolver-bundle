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
 * Thrown when hydration of property throws {@link \TypeError}.
 */
final class PropertyHydrationException extends \Exception
{
    public function __construct(
        public readonly string $className,
        public readonly string $propertyName,
        public readonly mixed $value,
        public readonly \TypeError $typeError
    ) {
        $message = sprintf(
            'Unable to assign value %s to property %s::$%s.',
            var_export($value, true),
            $className,
            $propertyName,
        );
        parent::__construct($message, previous: $typeError);
    }
}
