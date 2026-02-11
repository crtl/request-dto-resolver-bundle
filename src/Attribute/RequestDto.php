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

use Crtl\RequestDtoResolverBundle\RequestDtoResolver;

/**
 * Marks a class as request DTO which will be resolved and validated
 * by {@link RequestDtoResolver} for controller action arguments.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class RequestDto
{
    public function __construct(
        /**
         * Whether the DTO should be hydrated in strict mode.
         *
         * When null, the bundle-level `default_strict` configuration value is used.
         * If you want to attempt to coerce values instead pass `false` instead.
         */
        public readonly ?bool $strict = null,

        /**
         * Whether unset properties should default to null instead of being left uninitialized.
         *
         * When null, the bundle-level `default_null` configuration value is used.
         */
        public readonly ?bool $defaultNull = null,
    ) {
    }
}
