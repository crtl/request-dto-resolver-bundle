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
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute to resolve value for property of {@link RequestDto} from {@link Request::$query}.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class QueryParam extends AbstractNestedParam
{
    public const TRANSFORM_TYPE_INT = 'int';
    public const TRANSFORM_TYPE_FLOAT = 'float';
    public const TRANSFORM_TYPE_BOOL = 'bool';
    public const TRANSFORM_TYPE_STRING = 'string';

    public const TRANSFORM_TYPES = [
        self::TRANSFORM_TYPE_INT,
        self::TRANSFORM_TYPE_FLOAT,
        self::TRANSFORM_TYPE_BOOL,
        self::TRANSFORM_TYPE_STRING,
    ];

    /**
     * One of "int", "float", "bool", "string" or a callable that returns one of these.
     * The transform type can be used to transform the type of a query param when receiving it.
     * Defaults to "string".
     *
     * @var (callable(string): mixed)|string|null
     */
    public mixed $transformType = null;

    /**
     * @param (callable(string): mixed)|string $transformType
     */
    public function __construct(?string $name = null, callable|string|null $transformType = null)
    {
        parent::__construct($name);
        if (null !== $transformType) {
            $this->setTransformType($transformType);
        }
    }

    public function hasTransformType(): bool
    {
        return null !== $this->transformType;
    }

    /**
     * @internal
     */
    public function setTransformType(callable|string $transformType): void
    {
        assert(
            is_callable($transformType) || self::isTransformType($transformType),
            "transformType must be callable or one of: 'int', 'float', 'bool', 'string', ".get_debug_type($transformType).' given',
        );
        $this->transformType = $transformType;
    }

    /**
     * @param bool $transform Whether or not to transform the value using transformType
     */
    public function getValueFromRequest(Request $request, bool $transform = true): mixed
    {
        $value = parent::getValueFromRequest($request);

        if (null !== $value) {
            // TODO: rethink handling of arrays in query params
            return is_array($value) ? $value : $this->transformValue($value);
        }

        return null;
    }

    protected function getInputBag(Request $request): ParameterBag
    {
        return $request->query;
    }

    /**
     * @internal
     */
    public function transformValue(mixed $value): mixed
    {
        if (is_callable($this->transformType)) {
            return call_user_func($this->transformType, $value);
        }

        return match ($this->transformType) {
            self::TRANSFORM_TYPE_INT => filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            self::TRANSFORM_TYPE_FLOAT => filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE),
            self::TRANSFORM_TYPE_BOOL => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            default => (string) $value,
        };
    }

    public static function isTransformType(string $transformType): bool
    {
        return in_array($transformType, self::TRANSFORM_TYPES, true);
    }
}
